<?php

namespace App\Http\Controllers;

use App\Models\PortalPayment;
use App\Services\Payments\BkashGateway;
use App\Services\Whmcs\WhmcsClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(protected WhmcsClient $whmcs, protected BkashGateway $bkash) {}

    public function show(int $id): View
    {
        $client = session('whmcs_client', []);
        $invoice = $this->whmcs->invoice((int)($client['id'] ?? 0), $id);
        abort_unless($invoice, 404);
        return view('payment.show', compact('invoice'));
    }

    public function start(Request $request, int $id)
    {
        $client = session('whmcs_client', []);
        $clientId = (int)($client['id'] ?? 0);
        $invoice = $this->whmcs->invoice($clientId, $id);
        abort_unless($invoice, 404);

        $amount = (float)($invoice['balance'] ?? $invoice['total'] ?? 0);
        if (strtolower($invoice['status'] ?? '') === 'paid' || $amount <= 0) {
            return redirect()->route('invoice', $id)->withErrors(['payment'=>'Invoice is already paid.']);
        }

        $reference = 'INV-'.$id.'-'.Str::upper(Str::random(10));
        $payment = PortalPayment::create([
            'client_id'=>$clientId,'invoice_id'=>$id,'gateway'=>'bkash',
            'amount'=>$amount,'currency'=>'BDT','status'=>'pending','reference'=>$reference,
        ]);

        try {
            $result = $this->bkash->create($reference, (string)$amount);
            $payment->update(['payment_id'=>$result['paymentID'], 'payload'=>$result]);
            return redirect()->away($result['bkashURL']);
        } catch (Throwable $e) {
            $payment->update(['status'=>'failed','payload'=>['error'=>$e->getMessage()]]);
            return back()->withErrors(['payment'=>'Unable to start bKash payment.']);
        }
    }

    public function callback(Request $request, string $gateway)
    {
        if ($gateway !== 'bkash') abort(404);
        $paymentId = (string)$request->input('paymentID');
        if (!$paymentId) return redirect()->route('login')->withErrors(['payment'=>'Missing payment ID.']);

        $payment = PortalPayment::where('payment_id',$paymentId)->firstOrFail();
        if ($payment->status === 'paid') return redirect()->route('invoice',$payment->invoice_id);

        try {
            $result = $this->bkash->execute($paymentId);
            $status = $result['transactionStatus'] ?? '';
            $trxId = $result['trxID'] ?? null;
            $paidAmount = (float)($result['amount'] ?? 0);

            if (($result['statusCode'] ?? '') !== '0000' || $status !== 'Completed' || !$trxId || abs($paidAmount-(float)$payment->amount) > 0.01) {
                $payment->update(['status'=>'failed','payload'=>$result]);
                return redirect()->route('invoice',$payment->invoice_id)->withErrors(['payment'=>'Payment verification failed.']);
            }

            $invoice = $this->whmcs->invoice($payment->client_id, $payment->invoice_id);
            if (!$invoice) throw new \RuntimeException('Invoice not found.');

            $add = $this->whmcs->call('AddInvoicePayment', [
                'invoiceid'=>$payment->invoice_id,
                'transid'=>$trxId,
                'gateway'=>'bkash',
                'date'=>now()->format('Y-m-d H:i:s'),
                'amount'=>$paidAmount,
                'noemail'=>true,
            ]);
            if (($add['result'] ?? '') !== 'success') throw new \RuntimeException($add['message'] ?? 'WHMCS payment failed.');

            $payment->update([
                'status'=>'paid','transaction_id'=>$trxId,'completed_at'=>now(),'payload'=>$result,
            ]);

            return redirect()->route('invoice',$payment->invoice_id)->with('success','Payment completed successfully.');
        } catch (Throwable $e) {
            $payment->update(['status'=>'failed','payload'=>['error'=>$e->getMessage()]]);
            return redirect()->route('invoice',$payment->invoice_id)->withErrors(['payment'=>'Payment could not be verified.']);
        }
    }

    public function refund(int $id)
    {
        $client = session('whmcs_client', []);
        $payment = PortalPayment::where('id',$id)->where('client_id',(int)($client['id'] ?? 0))->firstOrFail();

        if ($payment->status !== 'paid' || !$payment->completed_at) {
            return back()->withErrors(['refund'=>'This payment is not refundable.']);
        }
        if ($payment->completed_at->lt(now()->subMinutes((int) \App\Models\AdminSetting::get('refund_window_minutes', config('services.refund.window_minutes', 5))))) {
            return back()->withErrors(['refund'=>'The 5-minute instant refund window has expired.']);
        }
        if ($payment->refund_status === 'completed') {
            return back()->withErrors(['refund'=>'This payment has already been refunded.']);
        }

        $invoice = $this->whmcs->invoice($payment->client_id, $payment->invoice_id);
        if (!$invoice) abort(404);

        if ($this->invoiceServiceIsActive($invoice, $payment->client_id)) {
            return back()->withErrors(['refund'=>'The service is already active, so instant refund is no longer available.']);
        }

        try {
            $result = $this->bkash->refund(
                (string)$payment->payment_id,
                (string)$payment->transaction_id,
                (string)$payment->amount,
                'WHMCS-'.$payment->invoice_id,
                'Service was not activated'
            );
            $ok = (($result['statusCode'] ?? '') === '0000') || (($result['refundStatus'] ?? '') === 'Completed');
            $payment->update([
                'refund_status'=>$ok ? 'completed' : 'failed',
                'refund_amount'=>$payment->amount,
                'refund_id'=>$result['refundTrxID'] ?? $result['trxID'] ?? null,
                'refund_payload'=>$result,
                'refunded_at'=>$ok ? now() : null,
            ]);
            return back()->with($ok ? 'success' : 'error', $ok ? 'Refund processed successfully.' : 'Refund request was not completed.');
        } catch (Throwable $e) {
            $payment->update(['refund_status'=>'failed','refund_payload'=>['error'=>$e->getMessage()]]);
            return back()->withErrors(['refund'=>'Refund request failed.']);
        }
    }

    protected function invoiceServiceIsActive(array $invoice, int $clientId): bool
    {
        foreach (($invoice['items']['item'] ?? []) as $item) {
            $serviceId = (int)($item['relid'] ?? 0);
            if ($serviceId > 0) {
                $service = $this->whmcs->service($clientId, $serviceId);
                if (strtolower($service['status'] ?? '') === 'active') return true;
            }
        }
        return false;
    }
}
