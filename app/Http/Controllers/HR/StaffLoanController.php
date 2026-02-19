<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\MonthlyDeduction;
use App\Models\HR\PayrollDeduction;
use App\Models\HR\StaffLoan;
use App\Models\HR\StaffLoanSchedule;
use App\Services\HR\StaffLoanService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StaffLoanController extends Controller
{
    public function index()
    {
        $loans = StaffLoan::with('employee')->orderByDesc('Id')->get();

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
        if ($loan->Status === 'Approved') {
            app(StaffLoanService::class)->ensureSchedule($loan);
        }
        $schedule = StaffLoanSchedule::where('StaffLoanID', $loan->Id)
            ->orderBy('InstallmentNo')
            ->get();

        return view('hr.payroll.loans.show', compact('loan', 'schedule'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'EmployeeID' => ['required','exists:t_HREmployees,Id'],
            'LoanRef' => ['nullable','string','max:100'],
            'Name' => ['required','string','max:150'],
            'Principal' => ['required','numeric','min:0.01'],
            'InterestRate' => ['nullable','numeric','min:0'],
            'TenureMonths' => ['required','integer','min:1','max:360'],
            'InstallmentAmount' => ['nullable','numeric','min:0.01'],
            'StartDate' => ['required','date'],
            'EndDate' => ['nullable','date','after_or_equal:StartDate'],
        ]);

        $employeeId = (int)$data['EmployeeID'];
        if (! empty($data['LoanRef'])) {
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

        // Calculate monthly installment amount
        $principal = (float)$data['Principal'];
        $interestRate = (float)($data['InterestRate'] ?? 0);
        $tenure = (int)$data['TenureMonths'];

        if ($interestRate == 0) {
            // No interest - simple division
            $data['InstallmentAmount'] = round($principal / $tenure, 2);
        } else {
            // Calculate with interest using reducing balance method (PMT formula)
            $monthlyRate = $interestRate / 100 / 12;
            $numerator = $monthlyRate * pow(1 + $monthlyRate, $tenure);
            $denominator = pow(1 + $monthlyRate, $tenure) - 1;
            $data['InstallmentAmount'] = round($principal * ($numerator / $denominator), 2);
        }

        $data['Balance'] = $data['Principal'];
        $data['Status'] = 'Pending';
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();
        if (empty($data['EndDate']) && ! empty($data['StartDate']) && ! empty($data['TenureMonths'])) {
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

        app(StaffLoanService::class)->ensureSchedule($loan);
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

    public function cancel($id)
    {
        $loan = StaffLoan::findOrFail($id);

        // Only allow cancellation for Approved loans
        if ($loan->Status !== 'Approved') {
            return redirect()->route('hr.payroll.loans.index')
                ->with('error', 'Only approved loans can be cancelled.');
        }

        // Check if any repayments have been made
        $deductions = MonthlyDeduction::where('StaffLoanID', $loan->Id)->get();

        // Check if any of these deductions have been processed in a payroll run
        $hasPayments = false;
        foreach ($deductions as $deduction) {
            // Check if this deduction appears in any payroll run line
            // We consider a payment made if the month/year has passed or if Balance has reduced
            if ($deduction->Month < now()->month && $deduction->Year <= now()->year) {
                // Check if this period has been processed
                $hasPayments = true;

                break;
            }
        }

        // If loan balance has changed from principal, payments have been made
        if ($loan->Balance < $loan->Principal) {
            $hasPayments = true;
        }

        if ($hasPayments) {
            return redirect()->route('hr.payroll.loans.index')
                ->with('error', 'Cannot cancel loan. Repayments have already started. Balance: ' . number_format($loan->Balance, 2));
        }

        // Cancel the loan
        $loan->update([
            'Status' => 'Cancelled',
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        // Delete all associated monthly deductions (since no payments have been made)
        MonthlyDeduction::where('StaffLoanID', $loan->Id)->delete();

        return redirect()->route('hr.payroll.loans.index')
            ->with('success', 'Loan cancelled successfully. All pending deductions have been removed.');
    }

    private function generateLoanRepaymentDeductions(StaffLoan $loan): void
    {
        $deduction = PayrollDeduction::where('Code', 'LOAN-REP')->first();
        if (! $deduction) {
            throw ValidationException::withMessages([
                'LoanRef' => 'Payroll deduction code LOAN-REP is not configured. Create it under HR Config → Statutory & Payroll Rules → Deductions.',
            ]);
        }

        $schedules = StaffLoanSchedule::where('StaffLoanID', $loan->Id)
            ->orderBy('InstallmentNo')
            ->get();
        if ($schedules->isEmpty()) {
            return;
        }

        foreach ($schedules as $schedule) {
            $dueDate = $schedule->DueDate;
            if (! $dueDate) {
                continue;
            }
            $month = (int)$dueDate->month;
            $year = (int)$dueDate->year;
            $amount = (float)($schedule->TotalDue ?? $loan->InstallmentAmount ?? 0);
            if ($amount <= 0) {
                continue;
            }
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
