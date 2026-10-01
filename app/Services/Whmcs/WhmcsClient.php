<?php

namespace App\Services\Whmcs;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhmcsClient
{
    public function __construct(protected string $url = '')
    {
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

        return $response->json() ?: ['result' => 'error', 'message' => 'Invalid WHMCS response'];
    }

    public function authenticateCustomer(string $email, string $password): array
    {
        $response = Http::timeout((int) config('services.whmcs.timeout', 15))
            ->acceptJson()
            ->post($this->url . '/api/v2/user/session', [
                'email' => $email,
                'password' => $password,
            ]);

        if ($response->failed()) {
            return ['authenticated' => false, 'message' => 'Invalid email or password.'];
        }

        $data = $response->json();

        if (!is_array($data)) {
            return ['authenticated' => false, 'message' => 'Invalid authentication response.'];
        }

        // WHMCS may require a separate 2FA verification step.
        if (($data['requiresVerification'] ?? false) || ($data['requires_2fa'] ?? false)) {
            return [
                'authenticated' => false,
                'requires_2fa' => true,
                'api_state' => $data['apiState'] ?? $data['api_state'] ?? null,
                'message' => 'Two-factor authentication is required.',
            ];
        }

        return [
            'authenticated' => true,
            'api_state' => $data['apiState'] ?? $data['api_state'] ?? null,
            'session' => $data,
        ];
    }

    public function verifyTwoFactor(string $state, string $code): array
    {
        $response = Http::timeout((int) config('services.whmcs.timeout', 15))
            ->acceptJson()
            ->withHeaders(['X-Api-State' => $state])
            ->post($this->url . '/api/v2/user/session/verify', [
                'fields' => ['key' => $code],
            ]);

        if ($response->failed()) {
            return ['authenticated' => false, 'message' => 'Two-factor verification failed.'];
        }

        $data = $response->json();

        if (!is_array($data)) {
            return ['authenticated' => false, 'message' => 'Invalid verification response.'];
        }

        return [
            'authenticated' => !empty($data['apiState']) || !empty($data['api_state']) || (($data['result'] ?? null) === 'success'),
            'api_state' => $data['apiState'] ?? $data['api_state'] ?? $state,
            'session' => $data,
            'message' => $data['message'] ?? null,
        ];
    }

    public function findClientByEmail(string $email): ?array
    {
        $data = $this->call('GetClients', ['search' => $email, 'limitnum' => 25]);

        if (($data['result'] ?? null) !== 'success') {
            return null;
        }

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
