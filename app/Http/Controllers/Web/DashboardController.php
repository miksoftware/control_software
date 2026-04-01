<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CustomProject;
use App\Models\MikposFeature;
use App\Models\MikposLicense;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $stats = [
            'total_clients'     => Client::count(),
            'resellers'         => Client::resellers()->count(),
            'final_clients'     => Client::finalClients()->count(),
            'active_licenses'   => MikposLicense::active()->count(),
            'free_licenses'     => MikposLicense::freePromotion()->count(),
            'pending_features'  => MikposFeature::active()->count(),
            'active_projects'   => CustomProject::active()->count(),
            'overdue_projects'  => CustomProject::overdue()->count(),
            'total_payments'    => Payment::count(),
            'revenue_this_month' => Payment::whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('amount'),
        ];

        $totalDebt = CustomProject::sum('contract_value') + MikposFeature::sum('total_cost');
        $totalPaid = Payment::sum('amount');
        $stats['pending_balance'] = max(0, $totalDebt - $totalPaid);

        $recentPayments = Payment::with('client')
            ->orderByDesc('paid_at')
            ->limit(5)
            ->get();

        $recentClients = Client::orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'recentPayments', 'recentClients'));
    }
}
