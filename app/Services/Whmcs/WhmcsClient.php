<?php

namespace App\Services\Whmcs;

use App\Models\AdminSetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhmcsClient
{
    protected string $url;
    protected int $timeout;

    public function __construct()
    {
        $this->url = rtrim((string) AdminSetting::get('whmcs_url', ''), '/');
        $this->timeout = (int) AdminSetting::get('whmcs_timeout', 15);
    }

    public function call(string $action, array $params = []): array
    {
        if ($this->url === '') {
            throw new RuntimeException('WHMCS API is not configured. Please configure it from Admin Settings.');
        }

        $payload = array_merge([
            'action' => $action,
            'identifier' => AdminSetting::get('whmcs_identifier', ''),
            'secret' => AdminSetting::get('whmcs_secret', ''),
            'responsetype' => 'json',
        ], $params);

        $response = Http::timeout($this->timeout)
            ->asForm()
            ->post($this->url . '/includes/api.php', $payload);

        if ($response->failed()) {
            throw new RuntimeException('WHMCS API request failed with HTTP '.$response->status());
        }

        return $response->json() ?: ['result' => 'error', 'message' => 'Invalid WHMCS response'];
    }

    public function authenticateCustomer(string $email, string $password): array
    {
        if ($this->url === '') {
            return ['authenticated' => false, 'message' => 'WHMCS API is not configured.'];
        }

        $response = Http::timeout($this->timeout)
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
        $response = Http::timeout($this->timeout)
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
        if (($data['result'] ?? null) !== 'success') return null;

        foreach (($data['clients']['client'] ?? []) as $client) {
            if (strcasecmp($client['email'] ?? '', $email) === 0) return $client;
        }

        return null;
    }

    public function service(int $clientId, int $serviceId): ?array
    {
        $data = $this->call('GetClientsProducts', ['clientid' => $clientId, 'serviceid' => $serviceId]);
        return $data['products']['product'][0] ?? null;
    }

    public function invoice(int $clientId, int $invoiceId): ?array
    {
        $data = $this->call('GetInvoice', ['invoiceid' => $invoiceId]);
        if (($data['result'] ?? null) !== 'success' || (int) ($data['userid'] ?? 0) !== $clientId) return null;
        return $data;
    }

    public function transactions(int $clientId): array
    {
        $data = $this->call('GetTransactions', ['clientid' => $clientId]);
        return $data['transactions']['transaction'] ?? [];
    }

    public function tickets(int $clientId): array
    {
        $data = $this->call('GetTickets', ['clientid' => $clientId, 'limitnum' => 50]);
        return $data['tickets']['ticket'] ?? [];
    }

    public function ticket(int $clientId, int $ticketId): ?array
    {
        $data = $this->call('GetTicket', ['ticketid' => $ticketId, 'repliessort' => 'ASC']);
        if (($data['result'] ?? null) !== 'success' || (int) ($data['userid'] ?? 0) !== $clientId) return null;
        return $data;
    }

    public function openTicket(int $clientId, array $data): array
    {
        return $this->call('OpenTicket', array_merge([
            'clientid' => $clientId,
            'deptid' => (int) AdminSetting::get('whmcs_support_dept_id', 1),
            'priority' => 'Medium',
        ], $data));
    }

    public function replyTicket(int $clientId, int $ticketId, string $message): array
    {
        return $this->call('AddTicketReply', [
            'ticketid' => $ticketId,
            'message' => $message,
            'clientid' => $clientId,
        ]);
    }

    public function dashboardSummary(?int $clientId): array
    {
        if (!$clientId) return ['services' => 0, 'domains' => 0, 'unpaid' => 0];

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
