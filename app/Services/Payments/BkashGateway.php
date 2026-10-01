<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class BkashGateway
{
    protected string $base;

    public function __construct()
    {
        $this->base = rtrim(config('services.bkash.base_url', 'https://tokenized.pay.bka.sh/v1.2.0-beta'), '/');
    }

    protected function token(): string
    {
        $r = Http::timeout((int) config('services.bkash.timeout', 20))
            ->acceptJson()
            ->withHeaders([
                'username' => config('services.bkash.username'),
                'password' => config('services.bkash.password'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-App-Key' => config('services.bkash.app_key'),
            ])->post($this->base.'/tokenized/checkout/token/grant', [
                'app_key' => config('services.bkash.app_key'),
                'app_secret' => config('services.bkash.app_secret'),
            ]);

        if ($r->failed() || !$r->json('id_token')) {
            throw new RuntimeException($r->json('statusMessage') ?: 'Unable to obtain bKash token.');
        }
        return $r->json('id_token');
    }

    protected function request(string $method, string $path, string $token, array $data = [])
    {
        $http = Http::timeout((int) config('services.bkash.timeout', 20))
            ->acceptJson()
            ->withHeaders([
                'Authorization' => $token,
                'X-App-Key' => config('services.bkash.app_key'),
                'Content-Type' => 'application/json',
            ]);
        return $method === 'get'
            ? $http->get($this->base.$path, $data)
            : $http->post($this->base.$path, $data);
    }

    public function create(string $reference, string $amount): array
    {
        $token = $this->token();
        $r = $this->request('post', '/tokenized/checkout/create', $token, [
            'mode' => '0011',
            'payerReference' => $reference,
            'callbackURL' => config('services.bkash.callback_url'),
            'amount' => number_format((float)$amount, 2, '.', ''),
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => $reference,
        ]);
        if ($r->failed() || !$r->json('paymentID')) {
            throw new RuntimeException($r->json('statusMessage') ?: 'Unable to create bKash payment.');
        }
        return $r->json();
    }

    public function execute(string $paymentId): array
    {
        $r = $this->request('post', '/tokenized/checkout/execute', $this->token(), ['paymentID'=>$paymentId]);
        if ($r->failed()) throw new RuntimeException($r->json('statusMessage') ?: 'Unable to execute bKash payment.');
        return $r->json();
    }

    public function refund(string $paymentId, string $trxId, string $amount, string $sku, string $reason): array
    {
        $r = $this->request('post', '/tokenized/checkout/payment/refund', $this->token(), [
            'paymentID' => $paymentId,
            'trxID' => $trxId,
            'amount' => number_format((float)$amount, 2, '.', ''),
            'sku' => $sku,
            'reason' => $reason,
        ]);
        if ($r->failed()) throw new RuntimeException($r->json('statusMessage') ?: 'bKash refund request failed.');
        return $r->json();
    }
}
