<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Every fund is visible to every signed-in user — this is a shared
        // team workspace, not a per-user portfolio.
        $funds = Fund::latest()->get();

        // Calculate portfolio metrics
        $totalValue = $funds->sum(function ($fund) {
            return $fund->data['value'] ?? 0;
        });

        $activeFunds = $funds->count();

        // Calculate average performance (if available)
        $performanceValues = $funds->filter(function ($fund) {
            return isset($fund->data['performance']) && is_string($fund->data['performance']);
        })->map(function ($fund) {
            return (float) str_replace('%', '', $fund->data['performance']);
        });

        $averagePerformance = $performanceValues->count() > 0
            ? round($performanceValues->average(), 1)
            : 0;

        return view('dashboard', compact('funds', 'totalValue', 'activeFunds', 'averagePerformance'));
    }
}
