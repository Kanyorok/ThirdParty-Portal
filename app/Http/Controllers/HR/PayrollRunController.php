<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\PayrollCycle;
use App\Models\HR\PayrollRun;
use App\Models\HR\PayrollRunLine;
use App\Models\HR\Employee;
use App\Models\HR\PayrollAllowance;
use App\Models\HR\MonthlyAllowance;
use App\Models\HR\MonthlyDeduction;
use App\Models\HR\EmployeeActingAssignment;
use App\Models\HR\AttendanceDaily;
use App\Models\HR\OvertimeRequest;
use App\Models\HR\LeaveRequest;
use App\Models\HR\WorkingDaySetting;
use App\Models\HR\Holiday;
use App\Models\HR\PayrollEmployerContribution;
use App\Models\HR\GratuityAccrual;
use App\Models\HR\GratuitySetting;
use App\Models\HR\Gratuity;
use App\Models\HR\StatutoryRelief;
use App\Models\HR\PayrollDeduction;
use App\Models\HR\PayrollDeductionRule;
use App\Models\Finance\SystemBankSetting;
use App\Models\Finance\BankAccount;
use App\Services\HR\PayrollMandatoryAllocator;
use App\Services\HR\PayrollFinancePostingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class PayrollRunController extends Controller
{
    protected array $workingMapCache = [];
    protected array $holidayCache = [];
    protected array $workingTotalsCache = [];

    public function index()
    {
        $runs = PayrollRun::with('cycle')->orderByDesc('Id')->paginate(30);
        return view('hr.payroll.runs.index', compact('runs'));
    }

    public function create()
    {
        $cycles = PayrollCycle::orderByDesc('Year')->orderByDesc('Month')->get();
        return view('hr.payroll.runs.create', compact('cycles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'PayrollCycleID' => ['required','exists:t_HRPayrollCycles,Id'],
            'Notes' => ['nullable','string','max:500'],
        ]);

        $existingRun = PayrollRun::where('PayrollCycleID', $data['PayrollCycleID'])->first();
        if ($existingRun) {
            throw ValidationException::withMessages([
                'PayrollCycleID' => 'A payroll run already exists for this cycle. Create a new cycle if you need a fresh run.',
            ]);
        }

        $run = PayrollRun::create([
            'PayrollCycleID' => $data['PayrollCycleID'],
            'Status' => 'Generated',
            'GeneratedOn' => now(),
            'GeneratedBy' => auth()->id(),
            'Notes' => $data['Notes'] ?? null,
            'CreatedOn' => now(),
            'CreatedBy' => auth()->id(),
        ]);

        $cycle = PayrollCycle::find($data['PayrollCycleID']);

        $employees = Employee::all(['Id','FirstName','LastName','GradeID','BasicSalary']);
        foreach ($employees as $emp) {
            $lineData = $this->buildPayrollLineData($cycle, $emp, $run);
            PayrollRunLine::create([
                'PayrollRunID' => $run->Id,
                'EmployeeID' => $emp->Id,
                'BasicSalary' => $lineData['BasicSalary'],
                'TotalAllowances' => $lineData['TotalAllowances'],
                'TotalDeductions' => $lineData['TotalDeductions'],
                'StatutoryDeductions' => $lineData['StatutoryDeductions'],
                'LoanDeductions' => $lineData['LoanDeductions'],
                'Overtime' => $lineData['Overtime'],
                'AttendanceAdjustments' => $lineData['AttendanceAdjustments'],
                'LeaveAdjustments' => $lineData['LeaveAdjustments'],
                'GrossPay' => $lineData['GrossPay'],
                'NetPay' => $lineData['NetPay'],
                'CreatedOn' => now(),
                'CreatedBy' => auth()->id(),
            ]);
        }

        return redirect()->route('hr.payroll.runs.show', $run->Id)->with('success', 'Payroll run generated (placeholder lines created).');
    }

    public function show($id)
    {
        $run = PayrollRun::with(['cycle','lines.employee'])->findOrFail($id);

        $allowancesByEmployee = collect();
        $deductionsByEmployee = collect();
        if ($run->cycle?->Month && $run->cycle?->Year) {
            $allowancesByEmployee = MonthlyAllowance::where('Month', $run->cycle->Month)
                ->where('Year', $run->cycle->Year)
                ->where('Status', 'Approved')
                ->orderBy('Id')
                ->get()
                ->groupBy('EmployeeID');

            $deductionsByEmployee = MonthlyDeduction::where('Month', $run->cycle->Month)
                ->where('Year', $run->cycle->Year)
                ->where('Status', 'Approved')
                ->orderBy('Id')
                ->get()
                ->groupBy('EmployeeID')
                ->map(function ($rows) {
                    return $rows->groupBy(function ($row) {
                        return trim((string)($row->Name ?? $row->DeductionID));
                    })->map(function ($items) {
                        $first = $items->first();
                        $first->Amount = $items->sum('Amount');
                        return $first;
                    })->values();
                });
        }

        return view('hr.payroll.runs.show', compact('run', 'allowancesByEmployee', 'deductionsByEmployee'));
    }

    public function companySummary($id)
    {
        $run = PayrollRun::with(['cycle','lines.employee.branch','lines.employee.department'])->findOrFail($id);
        $totals = [
            'Basic' => $run->lines->sum('BasicSalary'),
            'Allowances' => $run->lines->sum('TotalAllowances'),
            'Deductions' => $run->lines->sum('TotalDeductions'),
            'Net' => $run->lines->sum('NetPay'),
        ];

        $allowanceTotals = collect();
        $deductionTotals = collect();
        if ($run->cycle?->Month && $run->cycle?->Year) {
            $allowanceTotals = MonthlyAllowance::where('Month', $run->cycle->Month)
                ->where('Year', $run->cycle->Year)
                ->where('Status', 'Approved')
                ->groupBy('Name')
                ->selectRaw('Name, SUM(Amount) as Total')
                ->orderBy('Name')
                ->get();

            $deductionTotals = MonthlyDeduction::where('Month', $run->cycle->Month)
                ->where('Year', $run->cycle->Year)
                ->where('Status', 'Approved')
                ->groupBy('Name')
                ->selectRaw('Name, SUM(Amount) as Total')
                ->orderBy('Name')
                ->get();
        }

        return view('hr.payroll.reports.company_summary', compact('run', 'totals', 'allowanceTotals', 'deductionTotals'));
    }

    public function masterRegister($id)
    {
        $run = PayrollRun::with(['cycle','lines.employee.branch','lines.employee.department'])->findOrFail($id);
        return view('hr.payroll.reports.master_register', compact('run'));
    }

    public function branchSummary($id)
    {
        $run = PayrollRun::with(['cycle','lines.employee.branch'])->findOrFail($id);
        $groups = $run->lines->groupBy(function ($line) {
            return $line->employee?->branch?->Name ?? 'Unassigned';
        });
        return view('hr.payroll.reports.branch_summary', compact('run', 'groups'));
    }

    public function departmentSummary($id)
    {
        $run = PayrollRun::with(['cycle','lines.employee.department'])->findOrFail($id);
        $groups = $run->lines->groupBy(function ($line) {
            return $line->employee?->department?->Name ?? 'Unassigned';
        });
        return view('hr.payroll.reports.department_summary', compact('run', 'groups'));
    }

    public function statutoryReturns($id)
    {
        $run = PayrollRun::with('cycle')->findOrFail($id);
        $deductions = PayrollDeduction::where('IsActive', 1)->orderBy('Name')->get(['Id','Code','Name']);
        return view('hr.payroll.reports.statutory_returns', compact('run', 'deductions'));
    }

    public function statutoryReturn($id, $code)
    {
        $run = PayrollRun::with('cycle')->findOrFail($id);
        $deduction = PayrollDeduction::where('Code', $code)->firstOrFail();

        $rows = collect();
        if ($run->cycle?->Month && $run->cycle?->Year) {
            $rows = MonthlyDeduction::with('employee')
                ->where('DeductionID', $deduction->Id)
                ->where('Month', $run->cycle->Month)
                ->where('Year', $run->cycle->Year)
                ->where('Status', 'Approved')
                ->orderBy('EmployeeID')
                ->get();
        }
        $total = $rows->sum('Amount');

        return view('hr.payroll.reports.statutory_return', compact('run', 'deduction', 'rows', 'total'));
    }

    public function approve($id)
    {
        $run = PayrollRun::findOrFail($id);
        if ($run->Status === 'Approved') {
            return redirect()->route('hr.payroll.runs.show', $run->Id)->with('success', 'Payroll run already approved.');
        }
        $run->update([
            'Status' => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'RejectedBy' => null,
            'RejectedOn' => null,
            'RejectionReason' => null,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);
        return redirect()->route('hr.payroll.runs.show', $run->Id)->with('success', 'Payroll run approved.');
    }

    public function reject($id, Request $request)
    {
        $data = $request->validate([
            'RejectionReason' => ['nullable','string','max:255'],
        ]);
        $run = PayrollRun::findOrFail($id);
        if ($run->Status === 'Rejected') {
            return redirect()->route('hr.payroll.runs.show', $run->Id)->with('success', 'Payroll run already rejected.');
        }
        $run->update([
            'Status' => 'Rejected',
            'RejectedBy' => auth()->id(),
            'RejectedOn' => now(),
            'RejectionReason' => $data['RejectionReason'] ?? null,
            'ApprovedBy' => null,
            'ApprovedOn' => null,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);
        return redirect()->route('hr.payroll.runs.show', $run->Id)->with('success', 'Payroll run rejected.');
    }

    public function recalcLine($id, $employeeId)
    {
        $run = PayrollRun::with('cycle')->findOrFail($id);
        if (!$run->cycle) {
            return redirect()->route('hr.payroll.runs.show', $run->Id)
                ->withErrors(['run' => 'Payroll cycle not found for this run.']);
        }
        if ($run->Status === 'Approved') {
            return redirect()->route('hr.payroll.runs.show', $run->Id)
                ->withErrors(['run' => 'Approved payroll runs cannot be recalculated.']);
        }

        $employee = Employee::findOrFail($employeeId);
        $lineData = $this->buildPayrollLineData($run->cycle, $employee, $run);

        $line = PayrollRunLine::firstOrNew([
            'PayrollRunID' => $run->Id,
            'EmployeeID' => $employee->Id,
        ]);
        $line->fill($lineData);
        if ($line->exists) {
            $line->ModifiedBy = auth()->id();
            $line->ModifiedOn = now();
        } else {
            $line->CreatedBy = auth()->id();
            $line->CreatedOn = now();
        }
        $line->save();

        return redirect()->route('hr.payroll.runs.show', $run->Id)->with('success', 'Payroll line recalculated.');
    }

    public function payslip($runId, $employeeId)
    {
        $run = PayrollRun::with('cycle')->findOrFail($runId);
        $employee = Employee::with(['branch', 'department', 'grade', 'bank', 'bankBranch'])->findOrFail($employeeId);
        $line = PayrollRunLine::where('PayrollRunID', $runId)
            ->where('EmployeeID', $employeeId)
            ->firstOrFail();

        $month = (int)($run->cycle?->Month ?? now()->month);
        $year = (int)($run->cycle?->Year ?? now()->year);

        $allowances = MonthlyAllowance::where('EmployeeID', $employeeId)
            ->where('Month', $month)
            ->where('Year', $year)
            ->where('Status', 'Approved')
            ->orderBy('Name')
            ->get();

        $deductions = MonthlyDeduction::with('deduction')
            ->where('EmployeeID', $employeeId)
            ->where('Month', $month)
            ->where('Year', $year)
            ->where('Status', 'Approved')
            ->orderBy('Name')
            ->get()
            ->filter(function ($row) {
                return !$row->deduction || $row->deduction->ShowInPayslip;
            });

        $company = SystemBankSetting::query()->orderByDesc('Id')->first();

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('hr.payroll.reports.payslip', compact(
            'run',
            'employee',
            'line',
            'allowances',
            'deductions',
            'company'
        ));

        $fileName = "payslip_{$employee->EmployeeNo}_{$year}_".str_pad((string)$month, 2, '0', STR_PAD_LEFT).".pdf";
        return $pdf->download($fileName);
    }

    public function p9($runId, $employeeId)
    {
        $run = PayrollRun::with('cycle')->findOrFail($runId);
        $year = (int)($run->cycle?->Year ?? now()->year);
        $employee = Employee::with(['branch', 'department', 'grade', 'bank', 'bankBranch'])->findOrFail($employeeId);
        $company = SystemBankSetting::query()->orderByDesc('Id')->first();

        $runsByMonth = PayrollRun::with('cycle')
            ->whereHas('cycle', function ($q) use ($year) {
                $q->where('Year', $year);
            })
            ->get()
            ->keyBy(function ($row) {
                return (int)($row->cycle?->Month ?? 0);
            });

        $rows = [];
        $totals = [
            'Basic' => 0,
            'TaxableAllowances' => 0,
            'TaxablePay' => 0,
            'TaxCharged' => 0,
            'PersonalRelief' => 0,
            'PAYE' => 0,
            'Net' => 0,
        ];

        foreach (range(1, 12) as $month) {
            $monthRun = $runsByMonth->get($month);
            $cycle = $monthRun?->cycle;
            $line = $monthRun
                ? PayrollRunLine::where('PayrollRunID', $monthRun->Id)->where('EmployeeID', $employeeId)->first()
                : null;

            $basic = (float)($line?->BasicSalary ?? 0);
            $taxableAllowances = MonthlyAllowance::where('EmployeeID', $employeeId)
                ->where('Month', $month)
                ->where('Year', $year)
                ->where('Status', 'Approved')
                ->where('IsTaxable', 1)
                ->sum('Amount');
            $allAllowances = MonthlyAllowance::where('EmployeeID', $employeeId)
                ->where('Month', $month)
                ->where('Year', $year)
                ->where('Status', 'Approved')
                ->sum('Amount');
            $taxablePay = $basic + (float)$taxableAllowances;

            $taxCharged = 0.0;
            $personalRelief = 0.0;
            $payeTax = 0.0;
            if ($cycle) {
                $gross = $basic + (float)$allAllowances;
                $taxCharged = $this->calculatePayeTaxCharged($taxablePay, $gross, $cycle, $employeeId);
                $activeReliefs = $this->getActiveReliefs($cycle);
                $personalReliefs = $activeReliefs->filter(function ($relief) {
                    if (strcasecmp((string)($relief->ApplyStage ?? 'PostTax'), 'PostTax') !== 0) {
                        return false;
                    }
                    if (strcasecmp((string)($relief->Code ?? ''), 'PERS') === 0) {
                        return true;
                    }
                    return stripos((string)($relief->Name ?? ''), 'personal') !== false;
                });
                foreach ($personalReliefs as $relief) {
                    $personalRelief += $this->calculateReliefAmount($relief, $gross, $taxablePay, $cycle, $employeeId);
                }
                $payeTax = max(0, $taxCharged - $personalRelief);
            }

            $net = (float)($line?->NetPay ?? 0);

            $rows[] = [
                'Month' => Carbon::create($year, $month, 1)->format('M'),
                'Basic' => $basic,
                'TaxableAllowances' => (float)$taxableAllowances,
                'TaxablePay' => $taxablePay,
                'TaxCharged' => (float)$taxCharged,
                'PersonalRelief' => (float)$personalRelief,
                'PAYE' => (float)$payeTax,
                'Net' => $net,
            ];

            $totals['Basic'] += $basic;
            $totals['TaxableAllowances'] += (float)$taxableAllowances;
            $totals['TaxablePay'] += $taxablePay;
            $totals['TaxCharged'] += (float)$taxCharged;
            $totals['PersonalRelief'] += (float)$personalRelief;
            $totals['PAYE'] += (float)$payeTax;
            $totals['Net'] += $net;
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('hr.payroll.reports.p9', compact(
            'employee',
            'company',
            'rows',
            'totals',
            'year'
        ));

        $fileName = "p9_{$employee->EmployeeNo}_{$year}.pdf";
        return $pdf->download($fileName);
    }

    public function bankFile($runId)
    {
        $run = PayrollRun::with(['cycle', 'lines.employee.bank', 'lines.employee.bankBranch'])->findOrFail($runId);
        $month = (int)($run->cycle?->Month ?? now()->month);
        $year = (int)($run->cycle?->Year ?? now()->year);

        $header = ['EmployeeNo','EmployeeName','BankCode','BranchCode','AccountNumber','Amount','Currency','PaymentRef'];
        $rows = [];

        foreach ($run->lines as $line) {
            $employee = $line->employee;
            if (!$employee || ($employee->PaymentMode ?? 'Bank') !== 'Bank') {
                continue;
            }
            if (!$employee->BankAccount) {
                continue;
            }
            $bankCode = $employee->bank?->BankCode ?? $employee->bank?->ClearingCode ?? '';
            $branchCode = $employee->bankBranch?->BranchCode ?? '';
            $rows[] = [
                $employee->EmployeeNo,
                trim($employee->FirstName.' '.$employee->LastName),
                $bankCode,
                $branchCode,
                $employee->BankAccount,
                number_format((float)$line->NetPay, 2, '.', ''),
                'KES',
                "PAYROLL-{$run->Id}-{$employee->EmployeeNo}",
            ];
        }

        $fileName = "payroll_cbs_{$year}_".str_pad((string)$month, 2, '0', STR_PAD_LEFT).".csv";
        $callback = function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $header);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function eftXml($runId)
    {
        $run = PayrollRun::with(['cycle', 'lines.employee.bank', 'lines.employee.bankBranch'])->findOrFail($runId);
        $month = (int)($run->cycle?->Month ?? now()->month);
        $year = (int)($run->cycle?->Year ?? now()->year);

        $company = SystemBankSetting::query()->orderByDesc('Id')->first();
        $execDate = Carbon::create($year, $month, 1)->endOfMonth();
        $msgDate = $execDate->format('Ymd');

        $transactionsByBank = [];
        foreach ($run->lines as $line) {
            $employee = $line->employee;
            if (!$employee || ($employee->PaymentMode ?? 'Bank') !== 'Bank') {
                continue;
            }
            if (!$employee->BankAccount) {
                continue;
            }
            $bankId = (int)($employee->BankID ?? 0);
            if (!$bankId) {
                continue;
            }
            $transactionsByBank[$bankId][] = [
                'EmployeeNo' => $employee->EmployeeNo,
                'EmployeeName' => trim($employee->FirstName.' '.$employee->LastName),
                'BankCode' => $employee->bank?->SwiftCode ?? $employee->bank?->BankCode ?? $employee->bank?->ClearingCode ?? '',
                'BranchCode' => $employee->bankBranch?->BranchCode ?? '',
                'BranchName' => $employee->bankBranch?->BranchName ?? '',
                'AccountNumber' => $employee->BankAccount,
                'Amount' => number_format((float)$line->NetPay, 2, '.', ''),
                'Reference' => "PAYROLL-{$run->Id}-{$employee->EmployeeNo}",
                'TxId' => Str::uuid()->toString(),
                'EndToEndId' => $execDate->format('ymd') . str_pad((string)$employee->Id, 9, '0', STR_PAD_LEFT),
            ];
        }

        if (empty($transactionsByBank)) {
            return redirect()->route('hr.payroll.runs.show', $run->Id)
                ->withErrors(['eft' => 'No eligible payroll transactions found for EFT generation.']);
        }

        $missingAccounts = [];
        $files = [];
        foreach ($transactionsByBank as $bankId => $transactions) {
            $debtorAccount = BankAccount::with(['bank', 'branch'])
                ->where('BankID', $bankId)
                ->where('IsDefault', 1)
                ->where('IsActive', 1)
                ->first();
            if (!$debtorAccount) {
                $bankName = optional($run->lines->firstWhere('employee.BankID', $bankId)?->employee?->bank)->BankName;
                $missingAccounts[] = $bankName ? $bankName : "BankID {$bankId}";
                continue;
            }
            $companyBic = $debtorAccount->bank?->SwiftCode ?? $debtorAccount->bank?->BankCode ?? $debtorAccount->bank?->ClearingCode ?? '';
            $msgId = trim(($companyBic ?: 'PAYROLL') . $msgDate . 'RUN' . $run->Id);
            $xml = view('hr.payroll.reports.eft_xml', [
                'run' => $run,
                'month' => $month,
                'year' => $year,
                'company' => $company,
                'debtorAccount' => $debtorAccount,
                'transactions' => $transactions,
                'msgId' => $msgId,
            ])->render();
            $bankCode = $debtorAccount->bank?->BankCode ?? $debtorAccount->bank?->ClearingCode ?? $bankId;
            $fileName = $execDate->format('Ymd') . "_{$bankCode}_RUN{$run->Id}.CDL";
            $files[] = ['name' => $fileName, 'xml' => $xml];
        }

        if (!empty($missingAccounts)) {
            $message = 'Set a default company bank account in Finance for: ' . implode(', ', $missingAccounts) . '.';
            return redirect()->route('hr.payroll.runs.show', $run->Id)->withErrors(['eft' => $message]);
        }

        if (count($files) === 1) {
            return response($files[0]['xml'], 200, [
                'Content-Type' => 'application/xml',
                'Content-Disposition' => 'attachment; filename="'.$files[0]['name'].'"',
            ]);
        }

        $zipName = $execDate->format('Ymd') . "_RUN{$run->Id}_EFT.zip";
        $zipPath = tempnam(sys_get_temp_dir(), 'eft_');
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($files as $file) {
            $zip->addFromString($file['name'], $file['xml']);
        }
        $zip->close();

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    public function postToFinance($id, Request $request)
    {
        $run = PayrollRun::with(['cycle','lines.employee'])->findOrFail($id);
        $data = $request->validate([
            'posting_mode' => ['required','string','in:summary,branch_department'],
        ]);

        if ($run->FinanceJournalEntryID) {
            return redirect()->route('hr.payroll.runs.show', $run->Id)->with('success', 'Payroll run already linked to a Finance journal entry.');
        }

        $journal = app(PayrollFinancePostingService::class)->postRun($run, $data['posting_mode']);

        $run->update([
            'FinanceJournalEntryID' => $journal->Id,
            'FinancePostingMode' => $data['posting_mode'],
            'FinancePostedOn' => now(),
            'FinancePostedBy' => auth()->id(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.payroll.runs.show', $run->Id)->with('success', "Posted to Finance (Journal #{$journal->Id}) as draft.");
    }

    private function calculatePaye(float $taxableIncome, float $gross, ?PayrollCycle $cycle, int $employeeId): float
    {
        $tax = $this->calculatePayeTaxCharged($taxableIncome, $gross, $cycle, $employeeId);
        if (!$cycle) {
            return 0;
        }

        $activeReliefs = $this->getActiveReliefs($cycle);
        $postTaxReliefs = $activeReliefs->filter(function ($relief) {
            return strcasecmp((string)($relief->ApplyStage ?? 'PostTax'), 'PostTax') === 0;
        });
        $reliefTotal = 0.0;
        foreach ($postTaxReliefs as $relief) {
            $reliefTotal += $this->calculateReliefAmount($relief, $gross, $taxableIncome, $cycle, $employeeId);
        }

        $payable = max(0, $tax - $reliefTotal);
        return round($payable, 2);
    }

    private function calculatePayeTaxCharged(float $taxableIncome, float $gross, ?PayrollCycle $cycle, int $employeeId): float
    {
        $payeDeduction = PayrollDeduction::where('Code', 'PAYE')->first();
        if (!$payeDeduction || !$cycle) {
            return 0.0;
        }
        $rules = PayrollDeductionRule::where('DeductionID', $payeDeduction->Id)
            ->where('CalcMethod', 'PAYEOnTaxableIncome')
            ->where('IsActive', 1)
            ->orderBy('IncomeFrom')
            ->get();
        if ($rules->isEmpty()) {
            return 0.0;
        }

        $taxableIncome = max(0, $taxableIncome);
        $activeReliefs = $this->getActiveReliefs($cycle);
        $preTaxReliefs = $activeReliefs->filter(function ($relief) {
            return strcasecmp((string)($relief->ApplyStage ?? ''), 'PreTax') === 0;
        });

        foreach ($preTaxReliefs as $relief) {
            $taxableIncome -= $this->calculateReliefAmount($relief, $gross, $taxableIncome, $cycle, $employeeId);
        }
        $taxableIncome = max(0, $taxableIncome);

        $tax = 0.0;
        foreach ($rules as $rule) {
            $lower = (float)($rule->IncomeFrom ?? 0);
            $upper = $rule->IncomeTo !== null ? (float)$rule->IncomeTo : null;
            if ($taxableIncome <= $lower) {
                continue;
            }
            $bandAmount = $upper ? min($taxableIncome, $upper) - $lower : ($taxableIncome - $lower);
            if ($bandAmount < 0) {
                $bandAmount = 0;
            }
            if ($rule->Rate) {
                $tax += $bandAmount * ((float)$rule->Rate / 100);
            } elseif ($rule->Amount) {
                $tax += (float)$rule->Amount;
            }
        }

        return round($tax, 2);
    }

    private function getActiveReliefs(PayrollCycle $cycle)
    {
        $start = Carbon::create((int)$cycle->Year, (int)$cycle->Month, 1)->startOfMonth()->toDateString();
        $end = Carbon::create((int)$cycle->Year, (int)$cycle->Month, 1)->endOfMonth()->toDateString();

        return StatutoryRelief::where('IsActive', 1)
            ->where('EffectiveFrom', '<=', $end)
            ->where(function ($inner) use ($start) {
                $inner->whereNull('EffectiveTo')->orWhere('EffectiveTo', '>=', $start);
            })
            ->get();
    }

    private function calculateReliefAmount(StatutoryRelief $relief, float $gross, float $taxableIncome, PayrollCycle $cycle, int $employeeId): float
    {
        if ($relief->DeductionID) {
            $hasDeduction = MonthlyDeduction::where('EmployeeID', $employeeId)
                ->where('DeductionID', $relief->DeductionID)
                ->where('Month', $cycle->Month)
                ->where('Year', $cycle->Year)
                ->where('Status', 'Approved')
                ->exists();
            if (!$hasDeduction) {
                return 0.0;
            }

            $deduction = PayrollDeduction::find($relief->DeductionID);
            if (!$deduction || strcasecmp($deduction->Code, 'PAYE') === 0) {
                return 0.0;
            }
        }

        $type = $relief->ReliefType ?? 'Fixed';
        if (strcasecmp($type, 'Percentage') === 0) {
            if (!$relief->DeductionID || !$relief->ReliefRate) {
                return 0.0;
            }
            $deductionAmount = $this->calculateConfiguredDeductionAmount($deduction, $gross, $taxableIncome, $cycle, $employeeId);
            return max(0, $deductionAmount * ((float)$relief->ReliefRate / 100));
        }

        return max(0, (float)($relief->Amount ?? 0));
    }

    private function carryForwardRecurringDeductions(int $employeeId, int $month, int $year): void
    {
        $targetKey = ($year * 100) + $month;

        $recurring = MonthlyDeduction::where('EmployeeID', $employeeId)
            ->where('IsRecurring', 1)
            ->where('Status', 'Approved')
            ->get()
            ->groupBy('DeductionID');

        foreach ($recurring as $deductionId => $rows) {
            $latest = $rows->sortByDesc(function ($r) {
                return ((int)$r->Year * 100) + (int)$r->Month;
            })->first();
            if (!$latest) {
                continue;
            }
            $latestKey = ((int)$latest->Year * 100) + (int)$latest->Month;
            if ($latestKey >= $targetKey) {
                continue;
            }
            $exists = MonthlyDeduction::where('EmployeeID', $employeeId)
                ->where('DeductionID', $deductionId)
                ->where('Month', $month)
                ->where('Year', $year)
                ->exists();
            if ($exists) {
                continue;
            }
            MonthlyDeduction::create([
                'EmployeeID' => $employeeId,
                'DeductionID' => $deductionId,
                'Name' => $latest->Name,
                'Amount' => (float)$latest->Amount,
                'IsRecurring' => 1,
                'IsAutoCalculated' => (bool)($latest->IsAutoCalculated ?? false),
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

    private function calculateMonthlyDeductionsForEmployee(int $employeeId, float $gross, float $taxableIncome, ?PayrollCycle $cycle, int $month, int $year, float $basic, float $pensionableIncome): float
    {
        $rows = MonthlyDeduction::where('EmployeeID', $employeeId)
            ->where('Month', $month)
            ->where('Year', $year)
            ->where('Status', 'Approved')
            ->get();

        $total = 0;
        foreach ($rows as $row) {
            $deduction = $row->DeductionID ? PayrollDeduction::find($row->DeductionID) : null;
            if (!$deduction) {
                $total += (float)$row->Amount;
                continue;
            }

            $amount = (float)$row->Amount;
            if ($row->IsAutoCalculated) {
                $amount = $this->calculateConfiguredDeductionAmount($deduction, $gross, $taxableIncome, $cycle, $employeeId, $basic, $pensionableIncome);
                $row->update([
                    'Amount' => $amount,
                    'ModifiedBy' => auth()->id(),
                    'ModifiedOn' => now(),
                ]);
            }

            $total += $amount;
        }

        return round($total, 2);
    }

    private function calculateConfiguredDeductionAmount(PayrollDeduction $deduction, float $gross, float $taxableIncome, ?PayrollCycle $cycle, ?int $employeeId = null, float $basic = 0.0, float $pensionableIncome = 0.0): float
    {
        if (strcasecmp($deduction->Code, 'PAYE') === 0) {
            if (!$cycle) {
                return 0;
            }
            $employeeId = $employeeId ?? 0;
            return $this->calculatePaye($taxableIncome, $gross, $cycle, $employeeId);
        }

        if (!$cycle) {
            return 0;
        }
        $start = Carbon::create((int)$cycle->Year, (int)$cycle->Month, 1)->startOfMonth()->toDateString();
        $end = Carbon::create((int)$cycle->Year, (int)$cycle->Month, 1)->endOfMonth()->toDateString();

        $rules = PayrollDeductionRule::where('DeductionID', $deduction->Id)
            ->where('IsActive', 1)
            ->where('EffectiveFrom', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('EffectiveTo')->orWhere('EffectiveTo', '>=', $start);
            })
            ->orderBy('IncomeFrom')
            ->get();

        if ($rules->isEmpty()) {
            return 0;
        }

        $bandBase = $gross;
        $method = (string)($rules->first()?->CalcMethod ?? '');
        if ($method === 'PercentageOnBasic') {
            $bandBase = $basic;
        } elseif ($method === 'PercentageOnPensionable') {
            $bandBase = $pensionableIncome;
        }

        $rule = $rules->firstWhere(function ($r) use ($bandBase) {
            $from = (float)($r->IncomeFrom ?? 0);
            $to = $r->IncomeTo !== null ? (float)$r->IncomeTo : null;
            return $bandBase >= $from && ($to === null || $bandBase <= $to);
        }) ?? $rules->first();

        $amount = 0.0;
        switch ($rule->CalcMethod) {
            case 'PercentageOnGross':
                $amount = (float)$gross * ((float)($rule->Rate ?? 0) / 100);
                break;
            case 'PercentageOnBasic':
                $amount = (float)$basic * ((float)($rule->Rate ?? 0) / 100);
                break;
            case 'PercentageOnPensionable':
                $amount = (float)$pensionableIncome * ((float)($rule->Rate ?? 0) / 100);
                break;
            case 'Flat':
                $amount = (float)($rule->Amount ?? 0);
                break;
            case 'PercentageOnBand':
                $amount = (float)$gross * ((float)($rule->Rate ?? 0) / 100);
                break;
            case 'FlatOnBand':
                $amount = (float)($rule->Amount ?? 0);
                break;
            default:
                $amount = 0.0;
        }

        if ($rule->MinAmount !== null) {
            $amount = max($amount, (float)$rule->MinAmount);
        }
        if ($rule->MaxAmount !== null) {
            $amount = min($amount, (float)$rule->MaxAmount);
        }

        return round(max(0, $amount), 2);
    }

    private function buildPayrollLineData(PayrollCycle $cycle, Employee $emp, ?PayrollRun $run = null): array
    {
        $month = (int)($cycle->Month ?? now()->month);
        $year = (int)($cycle->Year ?? now()->year);
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();
        $workingMap = $this->getWorkingMap();
        $holidays = $this->getHolidaySet($periodStart, $periodEnd);
        $workingTotals = $this->getWorkingTotals($periodStart, $periodEnd, $workingMap, $holidays);

        $basic = (float)($emp->BasicSalary ?? 0);
        $dailyRate = $workingTotals['fractions'] > 0 ? $basic / $workingTotals['fractions'] : 0.0;
        $hourlyRate = $workingTotals['hours'] > 0 ? $basic / $workingTotals['hours'] : 0.0;

        // Ensure mandatory allowances/deductions exist for this employee and month/year.
        app(PayrollMandatoryAllocator::class)->syncForEmployee($emp, $month, $year);

        // Auto-create acting allowance (percentage of acting reference)
        $acting = EmployeeActingAssignment::where('EmployeeID', $emp->Id)
            ->where('Status', 'Approved')
            ->where(function($q) use ($periodStart) {
                $q->whereNull('EndDate')->orWhere('EndDate','>=', $periodStart->toDateString());
            })
            ->where('StartDate','<=', $periodEnd->toDateString())
            ->first();
        if ($acting) {
            $actingAllowance = PayrollAllowance::with(['rules' => function($q){
                $q->where('IsActive',1)->orderByDesc('EffectiveFrom');
            }])->where('Code','ACTING')->first();
            if ($actingAllowance) {
                $ruleRate = optional($actingAllowance->rules->first())->Rate ?? ($acting->ActingAllowanceRate ?? 0);
                $exists = MonthlyAllowance::where('EmployeeID', $emp->Id)
                    ->where('AllowanceID', $actingAllowance->Id)
                    ->where('Month', $month)
                    ->where('Year', $year)
                    ->exists();
                if (!$exists) {
                    $reference = $acting->ActingReferenceSalary ?? ($emp->BasicSalary ?? 0);
                    $rate = ($ruleRate ?? 0) / 100;
                    $amount = max(0, $reference * $rate);
                    MonthlyAllowance::create([
                        'EmployeeID' => $emp->Id,
                        'AllowanceID' => $actingAllowance->Id,
                        'Name' => $actingAllowance->Name,
                        'Amount' => $amount,
                        'Month' => $month,
                        'Year' => $year,
                        'IsTaxable' => $actingAllowance->IsTaxable,
                        'Status' => 'Approved',
                        'IsRecurring' => 0,
                        'CreatedBy' => auth()->id(),
                        'CreatedOn' => now(),
                        'ApprovedBy' => auth()->id(),
                        'ApprovedOn' => now(),
                    ]);
                }
            }
        }

        // Carry forward recurring monthly deductions for this period
        $this->carryForwardRecurringDeductions($emp->Id, $month, $year);

        // Calculate gross, taxable income, deductions, and net
        $approvedAllowances = MonthlyAllowance::where('EmployeeID', $emp->Id)
            ->where('Status','Approved')
            ->where('Month', $month)
            ->where('Year', $year)
            ->sum('Amount');

        $taxableAllowances = MonthlyAllowance::where('EmployeeID', $emp->Id)
            ->where('Status','Approved')
            ->where('IsTaxable', 1)
            ->where('Month', $month)
            ->where('Year', $year)
            ->sum('Amount');

        $overtimeAmount = $this->calculateOvertimeAmount($emp->Id, $periodStart, $periodEnd, $hourlyRate);
        $attendanceAdjustment = $this->calculateAttendanceAdjustment($emp->Id, $periodStart, $periodEnd, $dailyRate, $workingMap, $holidays);
        $leaveAdjustment = $this->calculateUnpaidLeaveAdjustment($emp->Id, $periodStart, $periodEnd, $dailyRate, $workingMap, $holidays);

        $gross = $basic + (float)$approvedAllowances + $overtimeAmount + $attendanceAdjustment + $leaveAdjustment;
        $taxableIncome = $basic + (float)$taxableAllowances + $overtimeAmount + $attendanceAdjustment + $leaveAdjustment;

        $pensionableAllowances = MonthlyAllowance::where('EmployeeID', $emp->Id)
            ->where('Status','Approved')
            ->where('Month', $month)
            ->where('Year', $year)
            ->whereHas('allowance', function ($q) {
                $q->where('IsPensionable', 1);
            })
            ->sum('Amount');

        $pensionableIncome = $basic + (float)$pensionableAllowances;

        $deductionsTotal = $this->calculateMonthlyDeductionsForEmployee(
            $emp->Id,
            $gross,
            $taxableIncome,
            $cycle,
            $month,
            $year,
            $basic,
            $pensionableIncome
        );
        $totalDeductions = $deductionsTotal;
        $net = $gross - $totalDeductions;

        if ($run) {
            $this->syncEmployerContributions($run, $cycle, $emp, $basic, $gross, $month, $year);
            $this->syncGratuityAccrual($run, $cycle, $emp, $basic, $gross, $month, $year);
        }

        return [
            'BasicSalary' => $basic,
            'TotalAllowances' => $approvedAllowances,
            'TotalDeductions' => $totalDeductions,
            'StatutoryDeductions' => $totalDeductions,
            'LoanDeductions' => 0,
            'Overtime' => $overtimeAmount,
            'AttendanceAdjustments' => $attendanceAdjustment,
            'LeaveAdjustments' => $leaveAdjustment,
            'GrossPay' => $gross,
            'NetPay' => $net,
        ];
    }

    private function syncEmployerContributions(PayrollRun $run, PayrollCycle $cycle, Employee $emp, float $basic, float $gross, int $month, int $year): void
    {
        $rows = MonthlyDeduction::where('EmployeeID', $emp->Id)
            ->where('Month', $month)
            ->where('Year', $year)
            ->where('Status', 'Approved')
            ->whereNotNull('DeductionID')
            ->get();

        foreach ($rows as $row) {
            $deduction = PayrollDeduction::find($row->DeductionID);
            if (!$deduction || !$deduction->EmployerContributionEnabled) {
                continue;
            }

            $method = $deduction->EmployerCalcMethod ?: 'MatchEmployeeDeduction';
            $rate = $deduction->EmployerRate !== null ? (float)$deduction->EmployerRate : 0;
            $base = 0.0;
            $amount = 0.0;

            switch ($method) {
                case 'PercentageOfBasic':
                    $base = $basic;
                    $amount = $base * ($rate / 100);
                    break;
                case 'PercentageOfGross':
                    $base = $gross;
                    $amount = $base * ($rate / 100);
                    break;
                case 'Flat':
                    $amount = (float)($deduction->EmployerAmount ?? 0);
                    break;
                case 'MatchEmployeeDeduction':
                default:
                    $base = (float)$row->Amount;
                    $rate = $rate > 0 ? $rate : 100;
                    $amount = $base * ($rate / 100);
                    break;
            }

            $amount = round(max(0, $amount), 2);
            if ($amount <= 0) {
                PayrollEmployerContribution::where('PayrollRunID', $run->Id)
                    ->where('EmployeeID', $emp->Id)
                    ->where('DeductionID', $deduction->Id)
                    ->delete();
                continue;
            }

            PayrollEmployerContribution::updateOrCreate(
                [
                    'PayrollRunID' => $run->Id,
                    'EmployeeID' => $emp->Id,
                    'DeductionID' => $deduction->Id,
                ],
                [
                    'Month' => $month,
                    'Year' => $year,
                    'BaseAmount' => $base,
                    'Rate' => $rate,
                    'CalcMethod' => $method,
                    'Amount' => $amount,
                    'ModifiedBy' => auth()->id(),
                    'ModifiedOn' => now(),
                    'CreatedBy' => auth()->id(),
                    'CreatedOn' => now(),
                ]
            );
        }
    }

    private function syncGratuityAccrual(PayrollRun $run, PayrollCycle $cycle, Employee $emp, float $basic, float $gross, int $month, int $year): void
    {
        $setting = $this->resolveGratuitySetting($cycle, $emp);
        if (!$setting) {
            GratuityAccrual::where('PayrollRunID', $run->Id)
                ->where('EmployeeID', $emp->Id)
                ->delete();
            $this->refreshGratuitySummary($emp->Id, $year);
            return;
        }

        $rate = (float)($setting->RatePercent ?? 0);
        if ($rate <= 0) {
            GratuityAccrual::where('PayrollRunID', $run->Id)
                ->where('EmployeeID', $emp->Id)
                ->delete();
            $this->refreshGratuitySummary($emp->Id, $year);
            return;
        }

        $basis = strtoupper((string)($setting->CalcBasis ?? 'BASIC'));
        $baseAmount = $basis === 'GROSS' ? $gross : $basic;
        $amount = round(max(0, $baseAmount * ($rate / 100)), 2);
        if ($amount <= 0) {
            return;
        }

        GratuityAccrual::updateOrCreate(
            [
                'PayrollRunID' => $run->Id,
                'EmployeeID' => $emp->Id,
            ],
            [
                'Year' => $year,
                'Month' => $month,
                'RatePercent' => $rate,
                'CalcBasis' => $basis,
                'BaseAmount' => $baseAmount,
                'Amount' => $amount,
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]
        );

        $this->refreshGratuitySummary($emp->Id, $year, $rate);
    }

    private function refreshGratuitySummary(int $employeeId, int $year, ?float $rate = null): void
    {
        $totals = GratuityAccrual::where('EmployeeID', $employeeId)
            ->where('Year', $year)
            ->selectRaw('SUM(BaseAmount) as BaseTotal, SUM(Amount) as AmountTotal')
            ->first();

        $summary = Gratuity::firstOrNew(['EmployeeID' => $employeeId, 'Year' => $year]);
        if ($summary->Status === 'Paid') {
            return;
        }

        $summary->RatePercent = $rate ?? ($summary->RatePercent ?? 0);
        $summary->GrossPay = (float)($totals->BaseTotal ?? 0);
        $summary->Amount = (float)($totals->AmountTotal ?? 0);
        $summary->Status = ($summary->Amount ?? 0) > 0 ? 'Accrued' : 'Pending';
        $summary->ModifiedBy = auth()->id();
        $summary->ModifiedOn = now();
        if (!$summary->exists) {
            $summary->CreatedBy = auth()->id();
            $summary->CreatedOn = now();
        }
        $summary->save();
    }

    private function resolveGratuitySetting(PayrollCycle $cycle, Employee $emp): ?GratuitySetting
    {
        $start = Carbon::create((int)$cycle->Year, (int)$cycle->Month, 1)->startOfMonth()->toDateString();
        $end = Carbon::create((int)$cycle->Year, (int)$cycle->Month, 1)->endOfMonth()->toDateString();
        $category = $this->employeeCategory($emp);

        $settings = GratuitySetting::where('IsActive', 1)
            ->where(function ($q) use ($start, $end) {
                $q->whereNull('EffectiveFrom')->orWhere('EffectiveFrom', '<=', $end);
            })
            ->where(function ($q) use ($start) {
                $q->whereNull('EffectiveTo')->orWhere('EffectiveTo', '>=', $start);
            })
            ->orderByDesc('EffectiveFrom')
            ->get();

        return $settings->firstWhere('ApplyFor', $category)
            ?? $settings->firstWhere('ApplyFor', 'All')
            ?? $settings->firstWhere('ApplyFor', null);
    }

    private function employeeCategory(Employee $emp): string
    {
        $employment = strtolower(trim((string)($emp->EmploymentType ?? '')));
        $contract = strtolower(trim((string)($emp->ContractType ?? '')));
        $combined = trim($employment . ' ' . $contract);

        if ($combined == '') {
            return 'Regular';
        }
        if (str_contains($combined, 'contract')) {
            return 'Contract';
        }
        if (str_contains($combined, 'intern')) {
            return 'Intern';
        }
        return 'Regular';
    }

    private function getWorkingMap(): array
    {
        if (!empty($this->workingMapCache)) {
            return $this->workingMapCache;
        }

        $map = [];
        $records = WorkingDaySetting::all()->keyBy('DayOfWeek');
        foreach (range(0, 6) as $dow) {
            $rec = $records[$dow] ?? null;
            $defaultWorking = ($dow >= 1 && $dow <= 5);
            $workingFlag = $rec ? (bool)$rec->IsWorking : $defaultWorking;
            $fraction = $rec
                ? (float)($rec->DayFraction ?? ($workingFlag ? 1.0 : 0.0))
                : ($workingFlag ? 1.0 : 0.0);
            if ($workingFlag && $fraction <= 0) {
                $fraction = 0.5;
            }

            $hours = 0.0;
            if ($workingFlag) {
                $hours = $this->hoursFromTimes($rec?->StartTime, $rec?->EndTime);
                if ($hours <= 0) {
                    $hours = 8.0;
                }
                $hours = $hours * $fraction;
            }

            $map[$dow] = [
                'working' => $workingFlag,
                'fraction' => $fraction,
                'hours' => $hours,
            ];
        }

        $this->workingMapCache = $map;
        return $map;
    }

    private function getHolidaySet(Carbon $start, Carbon $end): array
    {
        $key = $start->format('Y-m');
        if (isset($this->holidayCache[$key])) {
            return $this->holidayCache[$key];
        }

        $rows = Holiday::whereNull('DeletedOn')
            ->where('IsActive', 1)
            ->where('Status', 'Approved')
            ->whereBetween('HolidayDate', [$start->toDateString(), $end->toDateString()])
            ->get();

        $recurring = Holiday::whereNull('DeletedOn')
            ->where('IsActive', 1)
            ->where('Status', 'Approved')
            ->where('IsRecurring', 1)
            ->get();

        $dates = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dateStr = $d->toDateString();
            if ($rows->firstWhere('HolidayDate', $dateStr)) {
                $dates[$dateStr] = true;
                continue;
            }
            if ($recurring->firstWhere(fn($h) => Carbon::parse($h->HolidayDate)->format('m-d') === $d->format('m-d'))) {
                $dates[$dateStr] = true;
            }
        }

        $this->holidayCache[$key] = $dates;
        return $dates;
    }

    private function getWorkingTotals(Carbon $start, Carbon $end, array $workingMap, array $holidays): array
    {
        $key = $start->format('Y-m');
        if (isset($this->workingTotalsCache[$key])) {
            return $this->workingTotalsCache[$key];
        }

        $fractions = 0.0;
        $hours = 0.0;
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dateStr = $d->toDateString();
            if (isset($holidays[$dateStr])) {
                continue;
            }
            $day = $workingMap[$d->dayOfWeek] ?? ['fraction' => 0, 'hours' => 0];
            if (($day['fraction'] ?? 0) <= 0) {
                continue;
            }
            $fractions += (float)$day['fraction'];
            $hours += (float)$day['hours'];
        }

        $totals = ['fractions' => $fractions, 'hours' => $hours];
        $this->workingTotalsCache[$key] = $totals;
        return $totals;
    }

    private function hoursFromTimes($start, $end): float
    {
        $start = $this->normalizeTime($start);
        $end = $this->normalizeTime($end);
        if (!$start || !$end) {
            return 0.0;
        }
        $startTime = Carbon::createFromFormat('H:i:s', $start);
        $endTime = Carbon::createFromFormat('H:i:s', $end);
        $minutes = $endTime->diffInMinutes($startTime, false);
        if ($minutes <= 0) {
            return 0.0;
        }
        return round($minutes / 60, 2);
    }

    private function normalizeTime($time): ?string
    {
        if ($time === null) {
            return null;
        }
        $value = trim((string)$time);
        if ($value === '') {
            return null;
        }
        $value = explode('.', $value)[0];
        return $value;
    }

    private function calculateOvertimeAmount(int $employeeId, Carbon $start, Carbon $end, float $hourlyRate): float
    {
        if ($hourlyRate <= 0) {
            return 0.0;
        }
        $approvedRequests = OvertimeRequest::where('EmployeeID', $employeeId)
            ->where('Status', 'Approved')
            ->whereBetween('WorkDate', [$start->toDateString(), $end->toDateString()])
            ->sum('HoursRequested');

        $hours = (float)$approvedRequests;
        if ($hours <= 0) {
            $hours = (float)AttendanceDaily::where('EmployeeID', $employeeId)
                ->whereBetween('WorkDate', [$start->toDateString(), $end->toDateString()])
                ->sum('OvertimeHours');
        }

        return round($hours * $hourlyRate, 2);
    }

    private function calculateAttendanceAdjustment(int $employeeId, Carbon $start, Carbon $end, float $dailyRate, array $workingMap, array $holidays): float
    {
        if ($dailyRate <= 0) {
            return 0.0;
        }

        $leaveDates = $this->getLeaveDateSet($employeeId, $start, $end, $workingMap, $holidays);
        $absenceFraction = 0.0;

        $absences = AttendanceDaily::where('EmployeeID', $employeeId)
            ->whereBetween('WorkDate', [$start->toDateString(), $end->toDateString()])
            ->where('Status', 'Absent')
            ->get(['WorkDate']);

        foreach ($absences as $row) {
            $date = Carbon::parse($row->WorkDate);
            $dateStr = $date->toDateString();
            if (isset($holidays[$dateStr]) || isset($leaveDates[$dateStr])) {
                continue;
            }
            $fraction = (float)($workingMap[$date->dayOfWeek]['fraction'] ?? 0);
            if ($fraction <= 0) {
                continue;
            }
            $absenceFraction += $fraction;
        }

        return round($absenceFraction * $dailyRate * -1, 2);
    }

    private function calculateUnpaidLeaveAdjustment(int $employeeId, Carbon $start, Carbon $end, float $dailyRate, array $workingMap, array $holidays): float
    {
        if ($dailyRate <= 0) {
            return 0.0;
        }

        $requests = LeaveRequest::with('type')
            ->where('EmployeeID', $employeeId)
            ->where('Status', 'Approved')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('StartDate', [$start->toDateString(), $end->toDateString()])
                  ->orWhereBetween('EndDate', [$start->toDateString(), $end->toDateString()])
                  ->orWhere(function ($inner) use ($start, $end) {
                      $inner->where('StartDate', '<=', $start->toDateString())
                            ->where('EndDate', '>=', $end->toDateString());
                  });
            })
            ->get();

        $unpaidDays = 0.0;
        foreach ($requests as $leave) {
            if ($leave->type?->IsPaid) {
                continue;
            }
            $leaveStart = Carbon::parse($leave->StartDate);
            $leaveEnd = Carbon::parse($leave->EndDate);
            if ($leaveEnd->lt($start) || $leaveStart->gt($end)) {
                continue;
            }
            $rangeStart = $leaveStart->lt($start) ? $start->copy() : $leaveStart->copy();
            $rangeEnd = $leaveEnd->gt($end) ? $end->copy() : $leaveEnd->copy();
            $unpaidDays += $this->calculateLeaveDaysForRange($leave, $rangeStart, $rangeEnd, $workingMap, $holidays);
        }

        return round($unpaidDays * $dailyRate * -1, 2);
    }

    private function calculateLeaveDaysForRange(LeaveRequest $leave, Carbon $start, Carbon $end, array $workingMap, array $holidays): float
    {
        if ($start->isSameDay($end)) {
            $dateStr = $start->toDateString();
            if (isset($holidays[$dateStr])) {
                return 0.0;
            }
            $fraction = (float)($workingMap[$start->dayOfWeek]['fraction'] ?? 0);
            if ($fraction <= 0) {
                return 0.0;
            }
            if ($leave->TotalDays !== null) {
                return min((float)$leave->TotalDays, $fraction);
            }
            return $fraction;
        }

        return $this->calculateWorkingDays($start, $end, $workingMap, $holidays);
    }

    private function calculateWorkingDays(Carbon $start, Carbon $end, array $workingMap, array $holidays): float
    {
        $total = 0.0;
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateStr = $date->toDateString();
            if (isset($holidays[$dateStr])) {
                continue;
            }
            $fraction = (float)($workingMap[$date->dayOfWeek]['fraction'] ?? 0);
            if ($fraction <= 0) {
                continue;
            }
            $total += $fraction;
        }

        return round($total, 2);
    }

    private function getLeaveDateSet(int $employeeId, Carbon $start, Carbon $end, array $workingMap, array $holidays): array
    {
        $dates = [];
        $requests = LeaveRequest::where('EmployeeID', $employeeId)
            ->where('Status', 'Approved')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('StartDate', [$start->toDateString(), $end->toDateString()])
                  ->orWhereBetween('EndDate', [$start->toDateString(), $end->toDateString()])
                  ->orWhere(function ($inner) use ($start, $end) {
                      $inner->where('StartDate', '<=', $start->toDateString())
                            ->where('EndDate', '>=', $end->toDateString());
                  });
            })
            ->get(['StartDate', 'EndDate', 'TotalDays']);

        foreach ($requests as $leave) {
            $leaveStart = Carbon::parse($leave->StartDate);
            $leaveEnd = Carbon::parse($leave->EndDate);
            if ($leaveEnd->lt($start) || $leaveStart->gt($end)) {
                continue;
            }
            $rangeStart = $leaveStart->lt($start) ? $start->copy() : $leaveStart->copy();
            $rangeEnd = $leaveEnd->gt($end) ? $end->copy() : $leaveEnd->copy();

            for ($date = $rangeStart->copy(); $date->lte($rangeEnd); $date->addDay()) {
                $dateStr = $date->toDateString();
                if (isset($holidays[$dateStr])) {
                    continue;
                }
                $fraction = (float)($workingMap[$date->dayOfWeek]['fraction'] ?? 0);
                if ($fraction <= 0) {
                    continue;
                }
                $dates[$dateStr] = true;
            }
        }

        return $dates;
    }
}



