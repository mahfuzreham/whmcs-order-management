<?php

namespace App\Http\Controllers;

use App\Services\Whmcs\WhmcsClient;

class DashboardController extends Controller
{
    public function __invoke(WhmcsClient $whmcs)
    {
        $client = session('whmcs_client');

        return view('dashboard', [
            'client' => $client,
            'summary' => $whmcs->dashboardSummary($client['id'] ?? null),
        ]);
    }
}
