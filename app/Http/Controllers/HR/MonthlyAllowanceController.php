<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\MonthlyAllowance;
use App\Models\HR\PayrollAllowance;
use Illuminate\Http\Request;

class MonthlyAllowanceController extends Controller
{
    public function index()
    {
        $allowances = MonthlyAllowance::with(['employee','allowance'])->orderByDesc('Id')->paginate(30);
        return view('hr.payroll.allowances.index', compact('allowances'));
    }

    public function create()
    {
        $employees = Employee::orderBy('FirstName')->get(['Id','FirstName','LastName']);
        $allowances = PayrollAllowance::where('IsActive',1)->orderBy('Name')->get();
        return view('hr.payroll.allowances.create', compact('employees','allowances'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'EmployeeID' => ['required','exists:t_HREmployees,Id'],
            'AllowanceID' => ['required','exists:t_HRPayrollAllowances,Id'],
            'Name' => ['nullable','string','max:150'],
            'Amount' => ['required','numeric'],
            'Month' => ['required','integer','min:1','max:12'],
            'Year' => ['required','integer','min:2000','max:2100'],
            'IsTaxable' => ['sometimes','boolean'],
        ]);
        $allowance = PayrollAllowance::find($data['AllowanceID']);
        $data['Name'] = $data['Name'] ?: ($allowance?->Name ?? 'Allowance');
        $data['IsTaxable'] = $request->has('IsTaxable') ? $request->boolean('IsTaxable') : ($allowance?->IsTaxable ?? true);
        $data['Status'] = 'Pending';
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();
        MonthlyAllowance::create($data);

        return redirect()->route('hr.payroll.allowances.index')->with('success', 'Allowance captured.');
    }
}
