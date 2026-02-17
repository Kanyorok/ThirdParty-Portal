<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeSalaryHistory;
use App\Models\HR\SalaryAdjustment;
use Illuminate\Http\Request;

class SalaryAdjustmentController extends Controller
{
    public function index()
    {
        $adjustments = SalaryAdjustment::with('employee')->orderByDesc('Id')->paginate(30);

        return view('hr.payroll.adjustments.index', compact('adjustments'));
    }

    public function create()
    {
        $employees = Employee::orderBy('FirstName')->get(['Id','FirstName','LastName']);

        return view('hr.payroll.adjustments.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'EmployeeID' => ['required','exists:t_HREmployees,Id'],
            'Type' => ['required','in:Increment,Decrement'],
            'Amount' => ['required','numeric','min:0'],
            'EffectiveDate' => ['required','date'],
            'Reason' => ['nullable','string','max:250'],
        ]);

        $data['Status'] = 'Pending';
        $data['RequestedOn'] = now();
        $data['RequestedBy'] = auth()->id();
        $data['CreatedOn'] = now();
        $data['CreatedBy'] = auth()->id();

        SalaryAdjustment::create($data);

        return redirect()->route('hr.payroll.adjustments.index')->with('success', 'Adjustment request captured.');
    }

    public function approve($id, Request $request)
    {
        $adj = SalaryAdjustment::findOrFail($id);
        $emp = Employee::find($adj->EmployeeID);
        $delta = $adj->Type === 'Increment' ? (float)$adj->Amount : -(float)$adj->Amount;
        $current = $emp ? (float)($emp->BasicSalary ?? 0) : 0;
        $newSalary = $current + $delta;
        if ($newSalary < 0) {
            $newSalary = 0;
        }

        // Always record history
        EmployeeSalaryHistory::create([
            'EmployeeID' => $adj->EmployeeID,
            'BasicSalary' => $newSalary,
            'EffectiveFrom' => $adj->EffectiveDate,
            'Notes' => trim('Salary adjustment ('.$adj->Type.' '.$adj->Amount.'). '.($adj->Reason ?? '')),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        // Apply immediately if effective date is today/past
        if ($emp && now()->toDateString() >= $adj->EffectiveDate) {
            $emp->update([
                'BasicSalary' => $newSalary,
                'ModifiedOn' => now(),
                'ModifiedBy' => auth()->id(),
            ]);
        }

        $adj->update([
            'Status' => 'Approved',
            'ApprovedOn' => now(),
            'ApprovedBy' => auth()->id(),
            'ModifiedOn' => now(),
            'ModifiedBy' => auth()->id(),
        ]);

        return redirect()->route('hr.payroll.adjustments.index')->with('success', 'Adjustment approved.');
    }

    public function reject($id, Request $request)
    {
        $adj = SalaryAdjustment::findOrFail($id);
        $adj->update([
            'Status' => 'Rejected',
            'ApprovedOn' => now(),
            'ApprovedBy' => auth()->id(),
            'ModifiedOn' => now(),
            'ModifiedBy' => auth()->id(),
        ]);

        return redirect()->route('hr.payroll.adjustments.index')->with('success', 'Adjustment rejected.');
    }
}
