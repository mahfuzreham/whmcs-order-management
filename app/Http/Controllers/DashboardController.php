<?php

namespace App\Http\Controllers;

use App\Services\Whmcs\WhmcsClient;

class DashboardController extends Controller
{
    public function __invoke(WhmcsClient $whmcs)
    {
        $client = session('whmcs_client');
        $clientId = (int) ($client['id'] ?? 0);

        $summary = $whmcs->dashboardSummary($clientId ?: null);
        $transactions = $clientId ? $whmcs->transactions($clientId) : [];
        $tickets = $clientId ? $whmcs->tickets($clientId) : [];

        return view('dashboard', [
            'client' => $client,
            'summary' => $summary,
            'recentTransactions' => array_slice($transactions, 0, 5),
            'recentTickets' => array_slice($tickets, 0, 5),
        ]);
    }
}
