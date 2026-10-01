<?php

namespace App\Http\Controllers;

use App\Services\Whmcs\WhmcsClient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function __construct(protected WhmcsClient $whmcs) {}

    public function services(): View
    {
        $client = session('whmcs_client', []);
        $data = $this->whmcs->call('GetClientsProducts', ['clientid' => $client['id'] ?? 0]);
        return view('portal.services', [
            'products' => $data['products']['product'] ?? [],
        ]);
    }

    public function service(int $id): View
    {
        $client = session('whmcs_client', []);
        $service = $this->whmcs->service((int) ($client['id'] ?? 0), $id);
        abort_unless($service, 404);
        return view('portal.service', compact('service'));
    }

    public function invoice(int $id): View
    {
        $client = session('whmcs_client', []);
        $invoice = $this->whmcs->invoice((int) ($client['id'] ?? 0), $id);
        abort_unless($invoice, 404);
        return view('portal.invoice', compact('invoice'));
    }

    public function transactions(): View
    {
        $client = session('whmcs_client', []);
        return view('portal.transactions', [
            'transactions' => $this->whmcs->transactions((int) ($client['id'] ?? 0)),
        ]);
    }

    public function tickets(): View
    {
        $client = session('whmcs_client', []);
        return view('portal.tickets', [
            'tickets' => $this->whmcs->tickets((int) ($client['id'] ?? 0)),
        ]);
    }

    public function ticket(int $id): View
    {
        $client = session('whmcs_client', []);
        $ticket = $this->whmcs->ticket((int) ($client['id'] ?? 0), $id);
        abort_unless($ticket, 404);
        return view('portal.ticket', compact('ticket'));
    }

    public function createTicket(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required','string','max:200'],
            'message' => ['required','string','max:10000'],
            'priority' => ['nullable','in:Low,Medium,High'],
        ]);
        $client = session('whmcs_client', []);
        $result = $this->whmcs->openTicket((int) ($client['id'] ?? 0), $data);
        if (($result['result'] ?? null) !== 'success') {
            return back()->withErrors(['ticket' => $result['message'] ?? 'Unable to open ticket.']);
        }
        return redirect()->route('tickets')->with('success', 'Support ticket created.');
    }

    public function replyTicket(Request $request, int $id)
    {
        $data = $request->validate(['message' => ['required','string','max:10000']]);
        $client = session('whmcs_client', []);
        $ticket = $this->whmcs->ticket((int) ($client['id'] ?? 0), $id);
        abort_unless($ticket, 404);
        $result = $this->whmcs->replyTicket((int) ($client['id'] ?? 0), $id, $data['message']);
        if (($result['result'] ?? null) !== 'success') {
            return back()->withErrors(['message' => $result['message'] ?? 'Unable to send reply.']);
        }
        return back()->with('success', 'Reply sent.');
    }

    public function domains(): View
    {
        $client = session('whmcs_client', []);
        $data = $this->whmcs->call('GetClientsDomains', ['clientid' => $client['id'] ?? 0]);
        return view('portal.domains', [
            'domains' => $data['domains']['domain'] ?? [],
        ]);
    }

    public function invoices(): View
    {
        $client = session('whmcs_client', []);
        $data = $this->whmcs->call('GetInvoices', ['userid' => $client['id'] ?? 0]);
        return view('portal.invoices', [
            'invoices' => $data['invoices']['invoice'] ?? [],
        ]);
    }
}
