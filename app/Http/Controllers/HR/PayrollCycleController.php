<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\PayrollCycle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PayrollCycleController extends Controller
{
    public function index()
    {
        $cycles = PayrollCycle::orderByDesc('Year')->orderByDesc('Month')->paginate(30);
        return view('hr.payroll.cycles.index', compact('cycles'));
    }

    public function create()
    {
        return view('hr.payroll.cycles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Year' => ['required','integer','min:2000','max:2100'],
            'Month' => ['required','integer','min:1','max:12', Rule::unique('t_HRPayrollCycles')->where(fn($q) => $q->where('Year', $request->Year))],
            'Notes' => ['nullable','string','max:500'],
        ]);

        $data['Status'] = 'Open';
        $data['OpenedOn'] = now();
        $data['OpenedBy'] = auth()->id();
        $data['CreatedOn'] = now();
        $data['CreatedBy'] = auth()->id();

        PayrollCycle::create($data);

        return redirect()->route('hr.payroll.cycles.index')->with('success', 'Payroll cycle opened.');
    }

    public function show($id)
    {
        $cycle = PayrollCycle::with('runs')->findOrFail($id);
        return view('hr.payroll.cycles.show', compact('cycle'));
    }

    public function close($id)
    {
        $cycle = PayrollCycle::findOrFail($id);
        $cycle->update([
            'Status' => 'Closed',
            'ClosedOn' => now(),
            'ClosedBy' => auth()->id(),
            'ModifiedOn' => now(),
            'ModifiedBy' => auth()->id(),
        ]);
        return redirect()->route('hr.payroll.cycles.index')->with('success', 'Payroll cycle closed.');
    }

    public function reopen($id)
    {
        $cycle = PayrollCycle::findOrFail($id);
        $cycle->update([
            'Status' => 'Reopened',
            'ReopenedOn' => now(),
            'ReopenedBy' => auth()->id(),
            'ModifiedOn' => now(),
            'ModifiedBy' => auth()->id(),
        ]);
        return redirect()->route('hr.payroll.cycles.index')->with('success', 'Payroll cycle reopened.');
    }
}
