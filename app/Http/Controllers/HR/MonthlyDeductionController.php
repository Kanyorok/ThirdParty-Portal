<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\MonthlyDeduction;
use Illuminate\Http\Request;

class MonthlyDeductionController extends Controller
{
    public function index()
    {
        $deductions = MonthlyDeduction::with('employee')->orderByDesc('Id')->paginate(30);
        return view('hr.payroll.deductions.index', compact('deductions'));
    }

    public function create()
    {
        $employees = Employee::orderBy('FirstName')->get(['Id','FirstName','LastName']);
        return view('hr.payroll.deductions.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'EmployeeID' => ['required','exists:t_HREmployees,Id'],
            'Name' => ['required','string','max:150'],
            'Amount' => ['required','numeric'],
            'Month' => ['required','integer','min:1','max:12'],
            'Year' => ['required','integer','min:2000','max:2100'],
        ]);
        $data['Status'] = 'Pending';
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();
        MonthlyDeduction::create($data);

        return redirect()->route('hr.payroll.deductions.index')->with('success', 'Deduction captured.');
    }
}
