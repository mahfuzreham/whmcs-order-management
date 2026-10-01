<?php

namespace App\Http\Controllers;

use App\Services\Whmcs\WhmcsClient;
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
