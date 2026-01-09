<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\MonthlyDeduction;
use App\Models\HR\PayrollDeduction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MonthlyDeductionController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $employees = Employee::where('IsActive', 1)->orderBy('FirstName')->get(['Id','FirstName','LastName']);
        $deductions = MonthlyDeduction::with(['deduction'])
            ->where('Month', $month)
            ->where('Year', $year)
            ->orderBy('EmployeeID')
            ->orderBy('Id')
            ->get();
        $deductionsByEmployee = $deductions->groupBy('EmployeeID');

        return view('hr.payroll.deductions.index', compact('employees', 'deductionsByEmployee', 'month', 'year'));
    }

    public function create()
    {
        $employees = Employee::orderBy('FirstName')->get(['Id','FirstName','LastName']);
        $deductions = PayrollDeduction::where('IsActive', 1)
            ->where('IsMandatory', 0)
            ->where('Code', '<>', 'LOAN-REP')
            ->orderBy('Name')
            ->get();
        return view('hr.payroll.deductions.create', compact('employees','deductions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'EmployeeID' => ['required','exists:t_HREmployees,Id'],
            'DeductionID' => ['required','exists:t_HRPayrollDeductions,Id'],
            'Name' => ['nullable','string','max:150'],
            'OverrideAmount' => ['nullable','boolean'],
            'Amount' => ['nullable','numeric','min:0','required_if:OverrideAmount,1'],
            'Month' => ['required','integer','min:1','max:12'],
            'Year' => ['required','integer','min:2000','max:2100'],
        ]);

        $deduction = PayrollDeduction::find($data['DeductionID']);
        if ($deduction && $deduction->Code === 'LOAN-REP') {
            throw ValidationException::withMessages([
                'DeductionID' => 'Staff loan repayments are added automatically when a staff loan is approved.',
            ]);
        }

        if (!$deduction) {
            throw ValidationException::withMessages([
                'DeductionID' => 'Invalid deduction selected.',
            ]);
        }

        $existing = MonthlyDeduction::where('EmployeeID', (int)$data['EmployeeID'])
            ->where('DeductionID', (int)$data['DeductionID'])
            ->whereNull('StaffLoanID')
            ->where('Month', (int)$data['Month'])
            ->where('Year', (int)$data['Year'])
            ->orderByDesc('Id')
            ->first();

        if ($existing && in_array((string)$existing->Status, ['Pending', 'Approved'], true)) {
            throw ValidationException::withMessages([
                'DeductionID' => 'This deduction is already assigned to the selected employee for the selected period.',
            ]);
        }

        $data['Name'] = $data['Name'] ?? ($deduction?->Name ?? 'Deduction');
        $data['Status'] = 'Pending';
        $data['IsRecurring'] = $request->boolean('IsRecurring', false);

        $overrideAmount = $request->boolean('OverrideAmount', false);
        $data['IsAutoCalculated'] = !$overrideAmount;
        $data['Amount'] = $overrideAmount ? (float)($data['Amount'] ?? 0) : 0;

        $now = now();
        if ($existing) {
            $existing->update([
                'Name' => $data['Name'],
                'Amount' => $data['Amount'],
                'IsRecurring' => $data['IsRecurring'],
                'IsAutoCalculated' => $data['IsAutoCalculated'],
                'Status' => 'Pending',
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => $now,
            ]);
        } else {
            $data['CreatedBy'] = auth()->id();
            $data['CreatedOn'] = $now;
            MonthlyDeduction::create($data);
        }

        return redirect()->route('hr.payroll.deductions.index')->with('success', 'Deduction captured.');
    }

    public function approve($id, Request $request)
    {
        $row = MonthlyDeduction::findOrFail($id);
        $row->update([
            'Status' => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);
        return redirect()->route('hr.payroll.deductions.index')->with('success', 'Deduction approved.');
    }

    public function reject($id, Request $request)
    {
        $row = MonthlyDeduction::findOrFail($id);
        $row->update([
            'Status' => 'Rejected',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);
        return redirect()->route('hr.payroll.deductions.index')->with('success', 'Deduction rejected.');
    }
}
