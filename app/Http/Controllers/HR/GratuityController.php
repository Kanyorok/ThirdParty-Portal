<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Gratuity;
use App\Models\HR\PayrollRunLine;
use App\Models\HR\Employee;
use Illuminate\Http\Request;

class GratuityController extends Controller
{
    public function index(Request $request)
    {
        $query = Gratuity::with('employee')->orderByDesc('Year')->orderByDesc('Id');
        if ($request->filled('year')) {
            $query->where('Year', (int)$request->year);
        }
        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }
        $rows = $query->paginate(50);
        return view('hr.payroll.gratuity.index', compact('rows'));
    }

    public function create()
    {
        $year = now()->year;
        return view('hr.payroll.gratuity.create', compact('year'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Year' => ['required','integer','min:2000','max:2100'],
            'RatePercent' => ['required','numeric','min:0','max:100'],
            'Notes' => ['nullable','string','max:255'],
        ]);

        $year = (int)$data['Year'];
        $rate = (float)$data['RatePercent'];

        $grossByEmployee = PayrollRunLine::whereHas('run.cycle', function ($q) use ($year) {
                $q->where('Year', $year);
            })
            ->groupBy('EmployeeID')
            ->selectRaw('EmployeeID, SUM(GrossPay) as GrossPay')
            ->get()
            ->keyBy('EmployeeID');

        $employees = Employee::whereIn('Id', $grossByEmployee->keys()->all())->get(['Id']);
        foreach ($employees as $emp) {
            $gross = (float)($grossByEmployee[$emp->Id]->GrossPay ?? 0);
            $amount = round($gross * ($rate / 100), 2);

            Gratuity::updateOrCreate(
                ['EmployeeID' => $emp->Id, 'Year' => $year],
                [
                    'RatePercent' => $rate,
                    'GrossPay' => $gross,
                    'Amount' => $amount,
                    'Status' => 'Pending',
                    'Notes' => $data['Notes'] ?? null,
                    'ModifiedBy' => auth()->id(),
                    'ModifiedOn' => now(),
                    'CreatedBy' => auth()->id(),
                    'CreatedOn' => now(),
                ]
            );
        }

        return redirect()->route('hr.payroll.gratuity.index')->with('success', 'Gratuity computed for the year.');
    }

    public function pay($id)
    {
        $row = Gratuity::findOrFail($id);
        if ($row->Status === 'Paid') {
            return redirect()->route('hr.payroll.gratuity.index')->with('success', 'Gratuity already marked paid.');
        }
        $row->update([
            'Status' => 'Paid',
            'PaidOn' => now(),
            'PaidBy' => auth()->id(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.payroll.gratuity.index')->with('success', 'Gratuity marked as paid.');
    }
}
