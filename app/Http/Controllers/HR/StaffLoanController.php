<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\MonthlyDeduction;
use App\Models\HR\PayrollDeduction;
use App\Models\HR\StaffLoan;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

    public function show($id)
    {
        $loan = StaffLoan::with('employee')->findOrFail($id);
        $deductions = MonthlyDeduction::with('deduction')
            ->where('StaffLoanID', $loan->Id)
            ->orderBy('Year')
            ->orderBy('Month')
            ->get();

        return view('hr.payroll.loans.show', compact('loan', 'deductions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'EmployeeID' => ['required','exists:t_HREmployees,Id'],
            'LoanRef' => ['nullable','string','max:100'],
            'Name' => ['required','string','max:150'],
            'Principal' => ['required','numeric'],
            'InterestRate' => ['nullable','numeric'],
            'TenureMonths' => ['required','integer','min:1','max:360'],
            'InstallmentAmount' => ['required','numeric','min:0.01'],
            'StartDate' => ['required','date'],
            'EndDate' => ['nullable','date','after_or_equal:StartDate'],
        ]);

        $employeeId = (int)$data['EmployeeID'];
        if (!empty($data['LoanRef'])) {
            $exists = StaffLoan::where('EmployeeID', $employeeId)
                ->where('LoanRef', $data['LoanRef'])
                ->whereIn('Status', ['Pending','Approved'])
                ->exists();
            if ($exists) {
                throw ValidationException::withMessages([
                    'LoanRef' => 'A loan with this reference already exists for this employee (pending/approved).',
                ]);
            }
        }

        $data['Balance'] = $data['Principal'];
        $data['Status'] = 'Pending';
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();
        if (empty($data['EndDate']) && !empty($data['StartDate']) && !empty($data['TenureMonths'])) {
            $data['EndDate'] = \Carbon\Carbon::parse($data['StartDate'])->startOfMonth()->addMonths(((int)$data['TenureMonths']) - 1)->endOfMonth()->toDateString();
        }

        StaffLoan::create($data);

        return redirect()->route('hr.payroll.loans.index')->with('success', 'Staff loan captured. Approve it to generate the payroll deductions schedule.');
    }

    public function approve($id)
    {
        $loan = StaffLoan::with('employee')->findOrFail($id);
        if ($loan->Status !== 'Pending') {
            return redirect()->route('hr.payroll.loans.index')->with('success', 'Loan already processed.');
        }

        $loan->update([
            'Status' => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        $this->generateLoanRepaymentDeductions($loan);

        return redirect()->route('hr.payroll.loans.index')->with('success', 'Loan approved and loan repayment deductions generated.');
    }

    public function reject($id)
    {
        $loan = StaffLoan::findOrFail($id);
        if ($loan->Status !== 'Pending') {
            return redirect()->route('hr.payroll.loans.index')->with('success', 'Loan already processed.');
        }

        $loan->update([
            'Status' => 'Rejected',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.payroll.loans.index')->with('success', 'Loan rejected.');
    }

    private function generateLoanRepaymentDeductions(StaffLoan $loan): void
    {
        $deduction = PayrollDeduction::where('Code', 'LOAN-REP')->first();
        if (!$deduction) {
            throw ValidationException::withMessages([
                'LoanRef' => 'Payroll deduction code LOAN-REP is not configured. Create it under HR Config → Statutory & Payroll Rules → Deductions.',
            ]);
        }

        $start = \Carbon\Carbon::parse($loan->StartDate)->startOfMonth();
        $tenure = (int)$loan->TenureMonths;
        $amount = (float)$loan->InstallmentAmount;

        for ($i = 0; $i < $tenure; $i++) {
            $m = $start->copy()->addMonths($i);
            $month = (int)$m->month;
            $year = (int)$m->year;

            $exists = MonthlyDeduction::where('EmployeeID', $loan->EmployeeID)
                ->where('DeductionID', $deduction->Id)
                ->where('StaffLoanID', $loan->Id)
                ->where('Month', $month)
                ->where('Year', $year)
                ->exists();
            if ($exists) {
                continue;
            }

            $label = $deduction->Name;
            if ($loan->LoanRef) {
                $label .= ' - ' . $loan->LoanRef;
            } elseif ($loan->Name) {
                $label .= ' - ' . $loan->Name;
            }

            MonthlyDeduction::create([
                'EmployeeID' => $loan->EmployeeID,
                'DeductionID' => $deduction->Id,
                'StaffLoanID' => $loan->Id,
                'Name' => $label,
                'Amount' => $amount,
                'IsRecurring' => false,
                'IsAutoCalculated' => false,
                'Month' => $month,
                'Year' => $year,
                'Status' => 'Approved',
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
                'ApprovedBy' => auth()->id(),
                'ApprovedOn' => now(),
            ]);
        }
    }
}
