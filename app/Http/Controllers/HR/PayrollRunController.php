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
use App\Models\HR\OvertimeRate;
use App\Models\HR\LeaveRequest;
use App\Models\HR\Holiday;
use App\Models\HR\PayrollEmployerContribution;
use App\Models\HR\GratuityAccrual;
use App\Models\HR\GratuitySetting;
use App\Models\HR\Gratuity;
use App\Models\HR\StatutoryRelief;
use App\Models\HR\PayrollDeduction;
use App\Models\HR\PayrollDeductionRule;
use App\Exports\HR\StatutoryReturnExport;
use App\Models\Finance\SystemBankSetting;
use App\Models\Finance\BankAccount;
use App\Models\Core\Branch;
use App\Models\Core\Country;
use App\Services\HR\PayrollMandatoryAllocator;
use App\Services\HR\PayrollFinancePostingService;
use App\Services\HR\StaffLoanService;
use App\Services\HR\WorkingDayResolver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

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
        $cycles = PayrollCycle::whereIn('Status', ['Open', 'Reopened'])
            ->orderByDesc('Year')
            ->orderByDesc('Month')
            ->get();
        return view('hr.payroll.runs.create', compact('cycles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'PayrollCycleID' => ['required','exists:t_HRPayrollCycles,Id'],
            'Notes' => ['nullable','string','max:500'],
        ]);

        $cycle = PayrollCycle::findOrFail($data['PayrollCycleID']);
        if (strcasecmp((string)($cycle->Status ?? ''), 'Closed') === 0) {
            throw ValidationException::withMessages([
                'PayrollCycleID' => 'This payroll cycle is closed. Reopen it before generating payroll.',
            ]);
        }

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

        $employees = Employee::where('IsActive', 1)
            ->whereNull('DeletedOn')
            ->where(function ($q) {
                $q->whereNull('Status')->orWhere('Status', '!=', 'Exited');
            })
            ->get(['Id','FirstName','LastName','GradeID','BasicSalary']);
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

    public function companySummaryExport($id)
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

        $rows = [
            ['Section' => 'Totals', 'Name' => 'Total Basic', 'Amount' => (float)($totals['Basic'] ?? 0)],
            ['Section' => 'Totals', 'Name' => 'Total Allowances', 'Amount' => (float)($totals['Allowances'] ?? 0)],
            ['Section' => 'Totals', 'Name' => 'Total Deductions', 'Amount' => (float)($totals['Deductions'] ?? 0)],
            ['Section' => 'Totals', 'Name' => 'Total Net', 'Amount' => (float)($totals['Net'] ?? 0)],
        ];

        foreach ($allowanceTotals as $row) {
            $rows[] = ['Section' => 'Allowances', 'Name' => (string)$row->Name, 'Amount' => (float)$row->Total];
        }
        foreach ($deductionTotals as $row) {
            $rows[] = ['Section' => 'Deductions', 'Name' => (string)$row->Name, 'Amount' => (float)$row->Total];
        }

        $columns = [
            ['key' => 'Section', 'label' => 'Section'],
            ['key' => 'Name', 'label' => 'Name'],
            ['key' => 'Amount', 'label' => 'Amount'],
        ];

        $month = (int)($run->cycle?->Month ?? now()->month);
        $year = (int)($run->cycle?->Year ?? now()->year);
        $filename = "company_summary_{$month}_{$year}.xlsx";

        return Excel::download(new StatutoryReturnExport($columns, $rows), $filename);
    }

    public function masterRegister($id)
    {
        $run = PayrollRun::with(['cycle','lines.employee.branch','lines.employee.department'])->findOrFail($id);
        return view('hr.payroll.reports.master_register', compact('run'));
    }

    public function masterRegisterExport($id)
    {
        $run = PayrollRun::with(['cycle','lines.employee.branch','lines.employee.department'])->findOrFail($id);
        $rows = $run->lines->map(function ($line) {
            return [
                'Employee' => trim(($line->employee?->FirstName ?? '') . ' ' . ($line->employee?->LastName ?? '')),
                'Branch' => $line->employee?->branch?->Name ?? '-',
                'Department' => $line->employee?->department?->Name ?? '-',
                'Basic' => (float)$line->BasicSalary,
                'Allowances' => (float)$line->TotalAllowances,
                'Deductions' => (float)$line->TotalDeductions,
                'NetPay' => (float)$line->NetPay,
            ];
        })->toArray();

        $columns = [
            ['key' => 'Employee', 'label' => 'Employee'],
            ['key' => 'Branch', 'label' => 'Branch'],
            ['key' => 'Department', 'label' => 'Department'],
            ['key' => 'Basic', 'label' => 'Basic'],
            ['key' => 'Allowances', 'label' => 'Allowances'],
            ['key' => 'Deductions', 'label' => 'Deductions'],
            ['key' => 'NetPay', 'label' => 'Net Pay'],
        ];

        $month = (int)($run->cycle?->Month ?? now()->month);
        $year = (int)($run->cycle?->Year ?? now()->year);
        $filename = "master_register_{$month}_{$year}.xlsx";

        return Excel::download(new StatutoryReturnExport($columns, $rows), $filename);
    }

    public function branchSummary($id)
    {
        $run = PayrollRun::with(['cycle','lines.employee.branch'])->findOrFail($id);
        $groups = $run->lines->groupBy(function ($line) {
            return $line->employee?->branch?->Name ?? 'Unassigned';
        });
        return view('hr.payroll.reports.branch_summary', compact('run', 'groups'));
    }

    public function branchSummaryExport($id)
    {
        $run = PayrollRun::with(['cycle','lines.employee.branch'])->findOrFail($id);
        $groups = $run->lines->groupBy(function ($line) {
            return $line->employee?->branch?->Name ?? 'Unassigned';
        });

        $rows = [];
        foreach ($groups as $branch => $lines) {
            foreach ($lines as $line) {
                $rows[] = [
                    'Branch' => $branch,
                    'Employee' => trim(($line->employee?->FirstName ?? '') . ' ' . ($line->employee?->LastName ?? '')),
                    'Basic' => (float)$line->BasicSalary,
                    'Allowances' => (float)$line->TotalAllowances,
                    'Deductions' => (float)$line->TotalDeductions,
                    'NetPay' => (float)$line->NetPay,
                ];
            }
            $rows[] = [
                'Branch' => $branch,
                'Employee' => 'Totals',
                'Basic' => (float)$lines->sum('BasicSalary'),
                'Allowances' => (float)$lines->sum('TotalAllowances'),
                'Deductions' => (float)$lines->sum('TotalDeductions'),
                'NetPay' => (float)$lines->sum('NetPay'),
            ];
        }

        $columns = [
            ['key' => 'Branch', 'label' => 'Branch'],
            ['key' => 'Employee', 'label' => 'Employee'],
            ['key' => 'Basic', 'label' => 'Basic'],
            ['key' => 'Allowances', 'label' => 'Allowances'],
            ['key' => 'Deductions', 'label' => 'Deductions'],
            ['key' => 'NetPay', 'label' => 'Net Pay'],
        ];

        $month = (int)($run->cycle?->Month ?? now()->month);
        $year = (int)($run->cycle?->Year ?? now()->year);
        $filename = "branch_summary_{$month}_{$year}.xlsx";

        return Excel::download(new StatutoryReturnExport($columns, $rows), $filename);
    }

    public function departmentSummary($id)
    {
        $run = PayrollRun::with(['cycle','lines.employee.department'])->findOrFail($id);
        $groups = $run->lines->groupBy(function ($line) {
            return $line->employee?->department?->Name ?? 'Unassigned';
        });
        return view('hr.payroll.reports.department_summary', compact('run', 'groups'));
    }

    public function departmentSummaryExport($id)
    {
        $run = PayrollRun::with(['cycle','lines.employee.department'])->findOrFail($id);
        $groups = $run->lines->groupBy(function ($line) {
            return $line->employee?->department?->Name ?? 'Unassigned';
        });

        $rows = [];
        foreach ($groups as $department => $lines) {
            foreach ($lines as $line) {
                $rows[] = [
                    'Department' => $department,
                    'Employee' => trim(($line->employee?->FirstName ?? '') . ' ' . ($line->employee?->LastName ?? '')),
                    'Basic' => (float)$line->BasicSalary,
                    'Allowances' => (float)$line->TotalAllowances,
                    'Deductions' => (float)$line->TotalDeductions,
                    'NetPay' => (float)$line->NetPay,
                ];
            }
            $rows[] = [
                'Department' => $department,
                'Employee' => 'Totals',
                'Basic' => (float)$lines->sum('BasicSalary'),
                'Allowances' => (float)$lines->sum('TotalAllowances'),
                'Deductions' => (float)$lines->sum('TotalDeductions'),
                'NetPay' => (float)$lines->sum('NetPay'),
            ];
        }

        $columns = [
            ['key' => 'Department', 'label' => 'Department'],
            ['key' => 'Employee', 'label' => 'Employee'],
            ['key' => 'Basic', 'label' => 'Basic'],
            ['key' => 'Allowances', 'label' => 'Allowances'],
            ['key' => 'Deductions', 'label' => 'Deductions'],
            ['key' => 'NetPay', 'label' => 'Net Pay'],
        ];

        $month = (int)($run->cycle?->Month ?? now()->month);
        $year = (int)($run->cycle?->Year ?? now()->year);
        $filename = "department_summary_{$month}_{$year}.xlsx";

        return Excel::download(new StatutoryReturnExport($columns, $rows), $filename);
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
        $data = $this->buildStatutoryReturnData($run, $deduction);

        return view('hr.payroll.reports.statutory_return', [
            'run' => $run,
            'deduction' => $deduction,
            'rows' => $data['rows'],
            'total' => $data['total'],
            'returnPayload' => $data['returnPayload'],
        ]);
    }

    public function statutoryReturnExport($id, $code)
    {
        $run = PayrollRun::with('cycle')->findOrFail($id);
        $deduction = PayrollDeduction::where('Code', $code)->firstOrFail();

        $data = $this->buildStatutoryReturnData($run, $deduction);
        $returnPayload = $data['returnPayload'];

        if (!empty($returnPayload)) {
            $columns = $returnPayload['columns'];
            $rows = $returnPayload['rows'];
        } else {
            $columns = [
                ['key' => 'Employee', 'label' => 'Employee'],
                ['key' => 'EmployeeNo', 'label' => 'Employee No'],
                ['key' => 'KraPin', 'label' => 'KRA PIN'],
                ['key' => 'Amount', 'label' => 'Amount'],
            ];
            $rows = $data['rows']->map(function ($row) {
                return [
                    'Employee' => trim(($row->employee?->FirstName ?? '') . ' ' . ($row->employee?->LastName ?? '')),
                    'EmployeeNo' => $row->employee?->EmployeeNo ?? '',
                    'KraPin' => $row->employee?->KRAPIN ?? '',
                    'Amount' => (float)($row->Amount ?? 0),
                ];
            })->toArray();
        }

        $month = (int)($run->cycle?->Month ?? now()->month);
        $year = (int)($run->cycle?->Year ?? now()->year);
        $safeCode = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string)$deduction->Code);
        $filename = "statutory_{$safeCode}_{$month}_{$year}.xlsx";

        return Excel::download(new StatutoryReturnExport($columns, $rows), $filename);
    }

    private function buildStatutoryReturnData(PayrollRun $run, PayrollDeduction $deduction): array
    {
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

        $returnPayload = null;
        if ($run->cycle?->Month && $run->cycle?->Year && $rows->isNotEmpty()) {
            $month = (int)$run->cycle->Month;
            $year = (int)$run->cycle->Year;
            $employeeIds = $rows->pluck('EmployeeID')->unique()->values();

            $employees = Employee::whereIn('Id', $employeeIds)->get()->keyBy('Id');
            $allowancesByEmployee = MonthlyAllowance::where('Month', $month)
                ->where('Year', $year)
                ->where('Status', 'Approved')
                ->get()
                ->groupBy('EmployeeID');
            $deductionsByEmployee = MonthlyDeduction::with('deduction')
                ->where('Month', $month)
                ->where('Year', $year)
                ->where('Status', 'Approved')
                ->get()
                ->groupBy('EmployeeID');
            $linesByEmployee = PayrollRunLine::where('PayrollRunID', $run->Id)
                ->whereIn('EmployeeID', $employeeIds)
                ->get()
                ->keyBy('EmployeeID');

            $sumDeduction = function ($deductions, array $needles): float {
                if ($deductions->isEmpty()) {
                    return 0.0;
                }
                $needles = array_map('strtoupper', $needles);
                return $deductions->filter(function ($row) use ($needles) {
                    $code = strtoupper(trim((string)($row->deduction?->Code ?? $row->Name ?? '')));
                    $name = strtoupper(trim((string)($row->Name ?? $row->deduction?->Name ?? '')));
                    foreach ($needles as $needle) {
                        if ($needle === '') {
                            continue;
                        }
                        if (str_contains($code, $needle) || str_contains($name, $needle)) {
                            return true;
                        }
                    }
                    return false;
                })->sum('Amount');
            };

            $returnCode = strtoupper((string)$deduction->Code);
            $rowsPayload = [];
            $columns = [];

            if ($returnCode === 'PAYE') {
                $columns = [
                    ['key' => 'KraPin', 'label' => 'EMPLOYEE KRA PIN'],
                    ['key' => 'Name', 'label' => 'EMPLOYEE NAME'],
                    ['key' => 'ResidentStatus', 'label' => 'RESIDENT STATUS'],
                    ['key' => 'EmployeeType', 'label' => 'TYPE OF EMPLOYEE'],
                    ['key' => 'PWD', 'label' => 'PWD'],
                    ['key' => 'Blank1', 'label' => 'BLANK'],
                    ['key' => 'GrossPay', 'label' => 'GROSS PAY', 'align' => 'end'],
                    ['key' => 'Zero1', 'label' => 'ZERO', 'align' => 'end'],
                    ['key' => 'Zero2', 'label' => 'ZERO', 'align' => 'end'],
                    ['key' => 'Zero3', 'label' => 'ZERO', 'align' => 'end'],
                    ['key' => 'HousingType', 'label' => 'TYPE OF HOUSING'],
                    ['key' => 'Blank2', 'label' => 'BLANK'],
                    ['key' => 'Zero4', 'label' => 'ZERO', 'align' => 'end'],
                    ['key' => 'Blank3', 'label' => 'BLANK'],
                    ['key' => 'Shif', 'label' => 'SHIF', 'align' => 'end'],
                    ['key' => 'NssfTier1', 'label' => 'NSSF TIER 1', 'align' => 'end'],
                    ['key' => 'Pension', 'label' => 'PENSION', 'align' => 'end'],
                    ['key' => 'Blank4', 'label' => ''],
                    ['key' => 'Blank5', 'label' => ''],
                    ['key' => 'HousingLevy', 'label' => 'HOUSING LEVY', 'align' => 'end'],
                ];

                foreach ($employeeIds as $employeeId) {
                    $employee = $employees->get($employeeId);
                    if (!$employee) {
                        continue;
                    }
                    $line = $linesByEmployee->get($employeeId);
                    $allowances = $allowancesByEmployee->get($employeeId, collect());
                    $grossPay = (float)($line?->GrossPay ?? (($line?->BasicSalary ?? 0) + $allowances->sum('Amount')));
                    $deductions = $deductionsByEmployee->get($employeeId, collect());
                    $shif = $sumDeduction($deductions, ['SHIF', 'SHA', 'NHIF']);
                    $housingLevy = $sumDeduction($deductions, ['HOUSING LEVY', 'AHL']);
                    $nssfTier1 = $sumDeduction($deductions, ['NSSF', 'TIER 1']);
                    if ($nssfTier1 <= 0) {
                        $nssfTier1 = $sumDeduction($deductions, ['NSSF']);
                    }
                    $pension = $sumDeduction($deductions, ['PENSION']);

                    $rowsPayload[] = [
                        'KraPin' => $employee->KRAPIN ?? '-',
                        'Name' => trim(($employee->FirstName ?? '').' '.($employee->LastName ?? '')),
                        'ResidentStatus' => 'Resident',
                        'EmployeeType' => $employee->EmploymentType ?: 'Primary Employee',
                        'PWD' => 'No',
                        'Blank1' => '',
                        'GrossPay' => $grossPay,
                        'Zero1' => 0,
                        'Zero2' => 0,
                        'Zero3' => 0,
                        'HousingType' => 'benefit not given',
                        'Blank2' => '',
                        'Zero4' => 0,
                        'Blank3' => '',
                        'Shif' => $shif,
                        'NssfTier1' => $nssfTier1,
                        'Pension' => $pension,
                        'Blank4' => '',
                        'Blank5' => '',
                        'HousingLevy' => $housingLevy,
                    ];
                }
                $returnPayload = ['type' => 'paye', 'columns' => $columns, 'rows' => $rowsPayload];
            } elseif (str_contains($returnCode, 'NSSF')) {
                $columns = [
                    ['key' => 'PayrollNo', 'label' => 'PAYROLL NUMBER'],
                    ['key' => 'Surname', 'label' => 'SURNAME'],
                    ['key' => 'OtherNames', 'label' => 'OTHER NAMES'],
                    ['key' => 'IdNo', 'label' => 'ID NO'],
                    ['key' => 'KraPin', 'label' => 'KRA PIN'],
                    ['key' => 'NssfNo', 'label' => 'NSSF NO'],
                    ['key' => 'GrossPay', 'label' => 'GROSS PAY', 'align' => 'end'],
                    ['key' => 'Voluntary', 'label' => 'VOLUNTARY', 'align' => 'end'],
                ];
                foreach ($employeeIds as $employeeId) {
                    $employee = $employees->get($employeeId);
                    if (!$employee) {
                        continue;
                    }
                    $line = $linesByEmployee->get($employeeId);
                    $allowances = $allowancesByEmployee->get($employeeId, collect());
                    $grossPay = (float)($line?->GrossPay ?? (($line?->BasicSalary ?? 0) + $allowances->sum('Amount')));
                    $deductions = $deductionsByEmployee->get($employeeId, collect());
                    $voluntary = $sumDeduction($deductions, ['VOLUNTARY']);
                    $rowsPayload[] = [
                        'PayrollNo' => $employee->EmployeeNo ?? '-',
                        'Surname' => $employee->LastName ?? '-',
                        'OtherNames' => trim(($employee->FirstName ?? '').' '.($employee->OtherNames ?? '')),
                        'IdNo' => $employee->EmployeeNo ?? '-',
                        'KraPin' => $employee->KRAPIN ?? '-',
                        'NssfNo' => $employee->NSSFNo ?? '-',
                        'GrossPay' => $grossPay,
                        'Voluntary' => $voluntary,
                    ];
                }
                $returnPayload = ['type' => 'nssf', 'columns' => $columns, 'rows' => $rowsPayload];
            } elseif (str_contains($returnCode, 'SHIF') || str_contains($returnCode, 'SHA') || str_contains($returnCode, 'NHIF')) {
                $columns = [
                    ['key' => 'PayrollNo', 'label' => 'PAYROLL NO'],
                    ['key' => 'FirstName', 'label' => 'FIRST NAME'],
                    ['key' => 'LastName', 'label' => 'LAST NAME'],
                    ['key' => 'IdType', 'label' => 'IDENTITY TYPE'],
                    ['key' => 'IdNo', 'label' => 'ID NO'],
                    ['key' => 'KraPin', 'label' => 'KRA PIN'],
                    ['key' => 'NhifNo', 'label' => 'NHIF / SHIF No'],
                    ['key' => 'Contribution', 'label' => 'CONTRIBUTION AMOUNT', 'align' => 'end'],
                    ['key' => 'Phone', 'label' => 'PHONE NUMBER'],
                ];
                foreach ($employeeIds as $employeeId) {
                    $employee = $employees->get($employeeId);
                    if (!$employee) {
                        continue;
                    }
                    $rowsPayload[] = [
                        'PayrollNo' => $employee->EmployeeNo ?? '-',
                        'FirstName' => $employee->FirstName ?? '-',
                        'LastName' => $employee->LastName ?? '-',
                        'IdType' => 'National ID',
                        'IdNo' => $employee->EmployeeNo ?? '-',
                        'KraPin' => $employee->KRAPIN ?? '-',
                        'NhifNo' => $employee->NHIFNo ?? '-',
                        'Contribution' => (float)$rows->firstWhere('EmployeeID', $employeeId)?->Amount ?? 0,
                        'Phone' => $employee->Phone ?? '-',
                    ];
                }
                $returnPayload = ['type' => 'shif', 'columns' => $columns, 'rows' => $rowsPayload];
            } elseif (str_contains($returnCode, 'HOUSING') || str_contains($returnCode, 'AHL')) {
                $columns = [
                    ['key' => 'IdNo', 'label' => 'EMPLOYEE ID NO'],
                    ['key' => 'Name', 'label' => 'NAME'],
                    ['key' => 'KraPin', 'label' => 'EMPLOYEE KRA PIN NO'],
                    ['key' => 'GrossPay', 'label' => 'GROSS PAY', 'align' => 'end'],
                ];
                foreach ($employeeIds as $employeeId) {
                    $employee = $employees->get($employeeId);
                    if (!$employee) {
                        continue;
                    }
                    $line = $linesByEmployee->get($employeeId);
                    $allowances = $allowancesByEmployee->get($employeeId, collect());
                    $grossPay = (float)($line?->GrossPay ?? (($line?->BasicSalary ?? 0) + $allowances->sum('Amount')));
                    $rowsPayload[] = [
                        'IdNo' => $employee->EmployeeNo ?? '-',
                        'Name' => trim(($employee->FirstName ?? '').' '.($employee->LastName ?? '')),
                        'KraPin' => $employee->KRAPIN ?? '-',
                        'GrossPay' => $grossPay,
                    ];
                }
                $returnPayload = ['type' => 'housing', 'columns' => $columns, 'rows' => $rowsPayload];
            }
        }

        return [
            'rows' => $rows,
            'total' => $total,
            'returnPayload' => $returnPayload,
        ];
    }

    public function approve($id)
    {
        $run = PayrollRun::with('cycle')->findOrFail($id);
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
        if ($run->cycle?->Month && $run->cycle?->Year) {
            app(StaffLoanService::class)->applyRepaymentsForMonth((int)$run->cycle->Month, (int)$run->cycle->Year, auth()->id());
        }
        if ($run->cycle && $run->cycle->Status !== 'Closed') {
            $run->cycle->update([
                'Status' => 'Closed',
                'ClosedOn' => now(),
                'ClosedBy' => auth()->id(),
                'ModifiedOn' => now(),
                'ModifiedBy' => auth()->id(),
            ]);
        }
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

    public function recalcAll($id)
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

        $employees = Employee::where('IsActive', 1)
            ->whereNull('DeletedOn')
            ->where(function ($q) {
                $q->whereNull('Status')->orWhere('Status', '!=', 'Exited');
            })
            ->get(['Id','FirstName','LastName','GradeID','BasicSalary']);

        $recalculated = 0;
        foreach ($employees as $employee) {
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
            $recalculated++;
        }

        return redirect()->route('hr.payroll.runs.show', $run->Id)
            ->with('success', "Payroll recalculated for {$recalculated} employee(s).");
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

        $allowances = MonthlyAllowance::where('EmployeeID', $employeeId)
            ->where('Year', $year)
            ->where('Status', 'Approved')
            ->get();
        $allowancesByMonth = $allowances->groupBy('Month');
        $taxableAllowancesByMonth = $allowances->where('IsTaxable', 1)->groupBy('Month');

        $deductionsByMonth = MonthlyDeduction::with('deduction')
            ->where('EmployeeID', $employeeId)
            ->where('Year', $year)
            ->where('Status', 'Approved')
            ->get()
            ->groupBy('Month');

        $sumDeduction = function ($rows, array $needles): float {
            if ($rows->isEmpty()) {
                return 0.0;
            }
            $needles = array_map('strtoupper', $needles);
            return $rows->filter(function ($row) use ($needles) {
                $code = strtoupper(trim((string)($row->deduction?->Code ?? $row->Name ?? '')));
                $name = strtoupper(trim((string)($row->Name ?? $row->deduction?->Name ?? '')));
                foreach ($needles as $needle) {
                    if ($needle === '') {
                        continue;
                    }
                    if (str_contains($code, $needle) || str_contains($name, $needle)) {
                        return true;
                    }
                }
                return false;
            })->sum('Amount');
        };

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
            'BenefitsNonCash' => 0,
            'ValueOfQuarters' => 0,
            'GrossPay' => 0,
            'PensionE1' => 0,
            'PensionE2' => 0,
            'PensionE3' => 0,
            'HousingLevy' => 0,
            'SHIF' => 0,
            'PRMF' => 0,
            'OwnerInterest' => 0,
            'TotalDeductions' => 0,
            'ChargeablePay' => 0,
            'TaxCharged' => 0,
            'PersonalRelief' => 0,
            'InsuranceRelief' => 0,
            'PAYE' => 0,
        ];

        foreach (range(1, 12) as $month) {
            $monthRun = $runsByMonth->get($month);
            $cycle = $monthRun?->cycle;
            $line = $monthRun
                ? PayrollRunLine::where('PayrollRunID', $monthRun->Id)->where('EmployeeID', $employeeId)->first()
                : null;

            $basic = (float)($line?->BasicSalary ?? 0);
            $monthAllowances = $allowancesByMonth->get($month, collect());
            $taxableAllowances = $taxableAllowancesByMonth->get($month, collect())->sum('Amount');
            $allAllowances = $monthAllowances->sum('Amount');
            $benefitsNonCash = (float)$taxableAllowances;
            $valueOfQuarters = 0.0;
            $grossPay = $basic + $benefitsNonCash + $valueOfQuarters;

            $monthDeductions = $deductionsByMonth->get($month, collect());
            $pensionActual = (float)$sumDeduction($monthDeductions, ['NSSF', 'PENSION', 'RETIREMENT']);
            $housingLevy = (float)$sumDeduction($monthDeductions, ['HOUSING LEVY', 'AHL']);
            $shif = (float)$sumDeduction($monthDeductions, ['SHIF', 'SHA', 'NHIF']);
            $prmf = (float)$sumDeduction($monthDeductions, ['PRMF']);
            $ownerInterest = (float)$sumDeduction($monthDeductions, ['OWNER OCCUPIED', 'OWNER-OCCUPIED', 'MORTGAGE']);
            $pensionE1 = round($basic * 0.30, 2);
            $pensionE3 = 30000.00;
            $totalDeductions = $pensionActual + $housingLevy + $shif + $prmf + $ownerInterest;
            $chargeablePay = max(0, $grossPay - $totalDeductions);

            $taxCharged = 0.0;
            $personalRelief = 0.0;
            $insuranceRelief = 0.0;
            $postTaxReliefTotal = 0.0;
            $payeTax = 0.0;
            if ($cycle) {
                $gross = $basic + (float)$allAllowances;
                $taxCharged = $this->calculatePayeTaxCharged($grossPay, $gross, $cycle, $employeeId);
                $activeReliefs = $this->getActiveReliefs($cycle);
                $postTaxReliefs = $activeReliefs->filter(function ($relief) {
                    if (strcasecmp((string)($relief->ApplyStage ?? 'PostTax'), 'PostTax') !== 0) {
                        return false;
                    }
                    return true;
                });
                $personalReliefs = $postTaxReliefs->filter(function ($relief) {
                    if (strcasecmp((string)($relief->Code ?? ''), 'PERS') === 0) {
                        return true;
                    }
                    return stripos((string)($relief->Name ?? ''), 'personal') !== false;
                });
                foreach ($postTaxReliefs as $relief) {
                    $postTaxReliefTotal += $this->calculateReliefAmount($relief, $gross, $grossPay, $cycle, $employeeId);
                }
                foreach ($personalReliefs as $relief) {
                    $personalRelief += $this->calculateReliefAmount($relief, $gross, $grossPay, $cycle, $employeeId);
                }
                $insuranceRelief = max(0, $postTaxReliefTotal - $personalRelief);
                $payeTax = max(0, $taxCharged - $postTaxReliefTotal);
            }

            $rows[] = [
                'Month' => Carbon::create($year, $month, 1)->format('M'),
                'Basic' => $basic,
                'BenefitsNonCash' => $benefitsNonCash,
                'ValueOfQuarters' => $valueOfQuarters,
                'GrossPay' => $grossPay,
                'PensionE1' => $pensionE1,
                'PensionE2' => $pensionActual,
                'PensionE3' => $pensionE3,
                'HousingLevy' => $housingLevy,
                'SHIF' => $shif,
                'PRMF' => $prmf,
                'OwnerInterest' => $ownerInterest,
                'TotalDeductions' => $totalDeductions,
                'ChargeablePay' => $chargeablePay,
                'TaxCharged' => (float)$taxCharged,
                'PersonalRelief' => (float)$personalRelief,
                'InsuranceRelief' => (float)$insuranceRelief,
                'PAYE' => (float)$payeTax,
            ];

            $totals['Basic'] += $basic;
            $totals['BenefitsNonCash'] += $benefitsNonCash;
            $totals['ValueOfQuarters'] += $valueOfQuarters;
            $totals['GrossPay'] += $grossPay;
            $totals['PensionE1'] += $pensionE1;
            $totals['PensionE2'] += $pensionActual;
            $totals['PensionE3'] += $pensionE3;
            $totals['HousingLevy'] += $housingLevy;
            $totals['SHIF'] += $shif;
            $totals['PRMF'] += $prmf;
            $totals['OwnerInterest'] += $ownerInterest;
            $totals['TotalDeductions'] += $totalDeductions;
            $totals['ChargeablePay'] += $chargeablePay;
            $totals['TaxCharged'] += (float)$taxCharged;
            $totals['PersonalRelief'] += (float)$personalRelief;
            $totals['InsuranceRelief'] += (float)$insuranceRelief;
            $totals['PAYE'] += (float)$payeTax;
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
        $workingMap = $this->getWorkingMap($emp->Id);
        $holidays = $this->getHolidaySet($periodStart, $periodEnd, $emp->Id);
        $workingTotals = $this->getWorkingTotals($periodStart, $periodEnd, $workingMap, $holidays, $emp->Id);

        $basic = (float)($emp->BasicSalary ?? 0);
        $dailyRate = $workingTotals['fractions'] > 0 ? $basic / $workingTotals['fractions'] : 0.0;
        $baseHourlyRate = $workingTotals['hours'] > 0 ? $basic / $workingTotals['hours'] : 0.0;
        $overtimeMultiplier = $this->resolveOvertimeMultiplier($emp->GradeID, $periodEnd);
        $overtimeHourlyRate = $baseHourlyRate * $overtimeMultiplier;

        $eligibleForPayroll = (int)($emp->IsActive ?? 1) === 1
            && (string)($emp->Status ?? '') !== 'Exited'
            && $emp->DeletedOn === null;

        if ($eligibleForPayroll) {
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
        }

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

        $overtimeAmount = $this->calculateOvertimeAmount($emp->Id, $periodStart, $periodEnd, $overtimeHourlyRate);
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

    private function getWorkingMap(?int $employeeId = null): array
    {
        $cacheKey = $employeeId ?? 0;
        if (isset($this->workingMapCache[$cacheKey])) {
            return $this->workingMapCache[$cacheKey];
        }

        $map = app(WorkingDayResolver::class)->getWorkingMap($employeeId);
        $this->workingMapCache[$cacheKey] = $map;
        return $map;
    }

    private function getHolidaySet(Carbon $start, Carbon $end, ?int $employeeId = null): array
    {
        $employeeKey = $employeeId ?? 0;
        $key = $start->format('Y-m');
        if (isset($this->holidayCache[$employeeKey][$key])) {
            return $this->holidayCache[$employeeKey][$key];
        }

        $employeeReligion = $this->getEmployeeReligion($employeeId);
        $employeeCountry = $this->getEmployeeCountry($employeeId);

        $rows = Holiday::whereNull('DeletedOn')
            ->where('IsActive', 1)
            ->where('Status', 'Approved')
            ->whereBetween('HolidayDate', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->filter(function ($holiday) use ($employeeReligion, $employeeCountry) {
                return $this->holidayAppliesToEmployee($employeeReligion, $employeeCountry, $holiday);
            });

        $recurring = Holiday::whereNull('DeletedOn')
            ->where('IsActive', 1)
            ->where('Status', 'Approved')
            ->where('IsRecurring', 1)
            ->get()
            ->filter(function ($holiday) use ($employeeReligion, $employeeCountry) {
                return $this->holidayAppliesToEmployee($employeeReligion, $employeeCountry, $holiday);
            });

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

        $this->holidayCache[$employeeKey][$key] = $dates;
        return $dates;
    }

    private function getEmployeeReligion(?int $employeeId): ?string
    {
        if (!$employeeId) {
            return null;
        }
        $religion = Employee::where('Id', $employeeId)->value('Religion');
        return $this->normalizeReligion($religion);
    }

    private function getEmployeeCountry(?int $employeeId): ?string
    {
        if (!$employeeId) {
            return null;
        }
        $branchId = Employee::where('Id', $employeeId)->value('BranchID');
        if (!$branchId) {
            return null;
        }
        $country = Branch::where('Id', $branchId)->value('Country');
        return $this->normalizeCountry($country);
    }

    private function holidayAppliesToEmployee(?string $employeeReligion, ?string $employeeCountry, Holiday $holiday): bool
    {
        return $this->holidayAppliesToReligion($employeeReligion, $holiday->AppliesToReligion ?? null)
            && $this->holidayAppliesToRegion($employeeCountry, $holiday->RegionScope ?? null, $holiday->CountryId ?? null);
    }

    private function holidayAppliesToReligion(?string $employeeReligion, ?string $holidayReligion): bool
    {
        $holiday = $this->normalizeReligion($holidayReligion);
        if (!$holiday) {
            return true;
        }
        if (!$employeeReligion) {
            return false;
        }
        return $holiday === $employeeReligion;
    }

    private function holidayAppliesToRegion(?string $employeeCountry, ?string $regionScope, ?int $holidayCountryId): bool
    {
        $scope = strtolower(trim((string)($regionScope ?? '')));
        if ($scope === '' || $scope === 'global') {
            return true;
        }
        if ($scope !== 'regional') {
            return true;
        }
        if (!$employeeCountry || !$holidayCountryId) {
            return false;
        }

        $countries = $this->getCountryLookup();
        $country = $countries[$holidayCountryId] ?? null;
        if (!$country) {
            return false;
        }

        $employee = $this->normalizeCountry($employeeCountry);
        if (!$employee) {
            return false;
        }

        $candidates = [
            $this->normalizeCountry($country->Name ?? null),
            $this->normalizeCountry($country->CountryCode ?? null),
            $this->normalizeCountry($country->Iso3 ?? null),
            $this->normalizeCountry((string)$holidayCountryId),
        ];

        return in_array($employee, array_filter($candidates), true);
    }

    private function getCountryLookup(): array
    {
        static $lookup = null;
        if ($lookup !== null) {
            return $lookup;
        }
        $lookup = Country::select(['Id', 'Name', 'CountryCode', 'Iso3'])->get()->keyBy('Id')->all();
        return $lookup;
    }

    private function normalizeReligion(?string $value): ?string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }
        return strtolower($value);
    }

    private function normalizeCountry(?string $value): ?string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }
        return strtolower($value);
    }

    private function getWorkingTotals(Carbon $start, Carbon $end, array $workingMap, array $holidays, ?int $employeeId = null): array
    {
        $key = $start->format('Y-m');
        $employeeKey = $employeeId ?? 0;
        if (isset($this->workingTotalsCache[$employeeKey][$key])) {
            return $this->workingTotalsCache[$employeeKey][$key];
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
        $this->workingTotalsCache[$employeeKey][$key] = $totals;
        return $totals;
    }

    private function resolveOvertimeMultiplier(?int $gradeId, Carbon $periodEnd): float
    {
        if (!$gradeId) {
            return 1.0;
        }

        $rate = OvertimeRate::where('GradeID', $gradeId)
            ->where('IsActive', 1)
            ->where(function ($q) use ($periodEnd) {
                $q->whereNull('EffectiveFrom')
                    ->orWhere('EffectiveFrom', '<=', $periodEnd->toDateString());
            })
            ->where(function ($q) use ($periodEnd) {
                $q->whereNull('EffectiveTo')
                    ->orWhere('EffectiveTo', '>=', $periodEnd->toDateString());
            })
            ->orderByDesc('EffectiveFrom')
            ->value('RateMultiplier');

        return $rate !== null ? (float)$rate : 1.0;
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
