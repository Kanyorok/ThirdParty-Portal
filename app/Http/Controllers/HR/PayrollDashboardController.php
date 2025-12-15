<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\PayrollCycle;
use App\Models\HR\PayrollRun;
use App\Models\HR\PayrollRunLine;

class PayrollDashboardController extends Controller
{
    public function index()
    {
        $openCycle = PayrollCycle::where('Status', 'Open')->orderByDesc('Year')->orderByDesc('Month')->first();
        $totals = [
            'cycles' => PayrollCycle::count(),
            'runs' => PayrollRun::count(),
            'employeesProcessed' => PayrollRunLine::count(),
            'openCycle' => $openCycle,
        ];

        $recentRuns = PayrollRun::with('cycle')->orderByDesc('Id')->limit(5)->get();
        $recentCycles = PayrollCycle::orderByDesc('Id')->limit(5)->get();

        return view('hr.payroll.dashboard', compact('totals', 'recentRuns', 'recentCycles'));
    }
}
