<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\StaffLoan;
use Illuminate\Http\Request;

class StaffLoanController extends Controller
{
    public function index()
    {
        $loans = StaffLoan::with('employee')->orderByDesc('Id')->paginate(30);
        return view('hr.payroll.loans.index', compact('loans'));
    }

    public function create()
    {
        $employees = Employee::orderBy('FirstName')->get(['Id','FirstName','LastName']);
        return view('hr.payroll.loans.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'EmployeeID' => ['required','exists:t_HREmployees,Id'],
            'Name' => ['required','string','max:150'],
            'Principal' => ['required','numeric'],
            'InterestRate' => ['nullable','numeric'],
            'TenureMonths' => ['nullable','integer','min:0'],
            'StartDate' => ['nullable','date'],
            'EndDate' => ['nullable','date','after_or_equal:StartDate'],
        ]);

        $data['InstallmentAmount'] = $request->input('InstallmentAmount', 0);
        $data['Balance'] = $data['Principal'];
        $data['Status'] = 'Pending';
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        StaffLoan::create($data);

        return redirect()->route('hr.payroll.loans.index')->with('success', 'Staff loan captured (schedule placeholder).');
    }
}
