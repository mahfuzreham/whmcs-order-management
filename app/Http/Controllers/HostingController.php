<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Services\Whmcs\WhmcsClient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HostingController extends Controller
{
    public function __construct(protected WhmcsClient $whmcs) {}

    public function index(): View
    {
        $client = session('whmcs_client', []);
        $data = $this->whmcs->call('GetClientsProducts', ['clientid' => (int) ($client['id'] ?? 0)]);

        $services = array_values(array_filter(
            $data['products']['product'] ?? [],
            fn ($service) => strtolower((string) ($service['status'] ?? '')) === 'active'
        ));

        return view('hosting.index', [
            'services' => $services,
            'customPanelEnabled' => AdminSetting::bool('hosting_panel_enabled', false),
        ]);
    }

    public function service(int $id): View
    {
        $client = session('whmcs_client', []);
        $service = $this->whmcs->service((int) ($client['id'] ?? 0), $id);
        abort_unless($service, 404);

        $customPanelEnabled = AdminSetting::bool('hosting_panel_enabled', false);

        return view('hosting.service', [
            'service' => $service,
            'customPanelEnabled' => $customPanelEnabled,
            'cpanelUrl' => $this->cpanelFallbackUrl($service),
        ]);
    }

    public function cpanel(int $id)
    {
        $client = session('whmcs_client', []);
        $service = $this->whmcs->service((int) ($client['id'] ?? 0), $id);
        abort_unless($service, 404);

        $url = $this->cpanelFallbackUrl($service);
        return redirect()->away($url);
    }

    private function cpanelFallbackUrl(array $service): string
    {
        $domain = trim((string) ($service['domain'] ?? $service['serverhostname'] ?? ''));
        $domain = preg_replace('#^https?://#i', '', $domain);
        $domain = trim((string) $domain, '/');

        $hostname = trim((string) ($service['serverhostname'] ?? ''));
        $hostname = preg_replace('#^https?://#i', '', $hostname);
        $hostname = trim((string) $hostname, '/');

        $host = $hostname ?: $domain;
        if ($host === '') {
            $host = parse_url((string) AdminSetting::get('whmcs_url', ''), PHP_URL_HOST) ?: 'localhost';
        }

        return 'https://'.$host.':2083/';
    }
}
