<?php

namespace App\Services\Whmcs;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhmcsClient
{
    public function __construct(
        protected string $url = ''
    ) {
        $this->url = rtrim(config('services.whmcs.url'), '/');
    }

    public function call(string $action, array $params = []): array
    {
        $payload = array_merge([
            'action' => $action,
            'identifier' => config('services.whmcs.identifier'),
            'secret' => config('services.whmcs.secret'),
            'responsetype' => 'json',
        ], $params);

        $response = Http::timeout((int) config('services.whmcs.timeout', 15))
            ->asForm()
            ->post($this->url . '/includes/api.php', $payload);

        if ($response->failed()) {
            throw new RuntimeException('WHMCS API request failed with HTTP '.$response->status());
        }

        $data = $response->json();

        if (!is_array($data) || ($data['result'] ?? null) !== 'success') {
            return $data ?: ['result' => 'error', 'message' => 'Invalid WHMCS response'];
        }

        return $data;
    }

    public function findClientByEmail(string $email): ?array
    {
        $data = $this->call('GetClients', ['email' => $email]);

        foreach (($data['clients']['client'] ?? []) as $client) {
            if (strcasecmp($client['email'] ?? '', $email) === 0) {
                return $client;
            }
        }

        return null;
    }

    public function dashboardSummary(?int $clientId): array
    {
        if (!$clientId) {
            return ['services' => 0, 'domains' => 0, 'unpaid' => 0];
        }

        $products = $this->call('GetClientsProducts', ['clientid' => $clientId]);
        $domains = $this->call('GetClientsDomains', ['clientid' => $clientId]);
        $invoices = $this->call('GetInvoices', ['userid' => $clientId, 'status' => 'Unpaid']);

        return [
            'services' => (int) ($products['totalresults'] ?? count($products['products']['product'] ?? [])),
            'domains' => (int) ($domains['totalresults'] ?? count($domains['domains']['domain'] ?? [])),
            'unpaid' => (int) ($invoices['totalresults'] ?? count($invoices['invoices']['invoice'] ?? [])),
        ];
    }
}
