<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\PayrollCycle;
use App\Models\HR\PayrollRun;
use App\Models\HR\PayrollRunLine;
use App\Models\HR\Employee;
use App\Models\HR\PayrollAllowance;
use App\Models\HR\MonthlyAllowance;
use App\Models\HR\EmployeeActingAssignment;
use Illuminate\Http\Request;

class PayrollRunController extends Controller
{
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

        // Simple placeholder: seed lines with employees and zero values
        $employees = Employee::all(['Id','FirstName','LastName','GradeID']);
        foreach ($employees as $emp) {
            PayrollRunLine::create([
                'PayrollRunID' => $run->Id,
                'EmployeeID' => $emp->Id,
                'BasicSalary' => 0,
                'TotalAllowances' => 0,
                'TotalDeductions' => 0,
                'StatutoryDeductions' => 0,
                'LoanDeductions' => 0,
                'Overtime' => 0,
                'AttendanceAdjustments' => 0,
                'LeaveAdjustments' => 0,
                'GrossPay' => 0,
                'NetPay' => 0,
                'CreatedOn' => now(),
                'CreatedBy' => auth()->id(),
            ]);

            // Auto-load mandatory allowances by grade for this period
            if ($cycle) {
                $month = $cycle->Month;
                $year = $cycle->Year;
                $gradeId = $emp->GradeID;
                $mandatory = PayrollAllowance::where('IsActive',1)
                    ->where('IsMandatory',1)
                    ->where(function($q) use ($gradeId) {
                        $q->whereHas('grades', function($g) use ($gradeId) {
                            $g->where('t_HRJobGrades.Id', $gradeId);
                        })->orWhereDoesntHave('grades');
                    })
                    ->get();
                foreach ($mandatory as $allowance) {
                    $exists = MonthlyAllowance::where('EmployeeID', $emp->Id)
                        ->where('AllowanceID', $allowance->Id)
                        ->where('Month', $month)
                        ->where('Year', $year)
                        ->exists();
                    if ($exists) {
                        continue;
                    }
                    // Default amount from first active rule amount if available
                    $defaultAmount = 0;
                    $rule = $allowance->rules()->where('IsActive',1)->orderByDesc('EffectiveFrom')->first();
                    if ($rule && $rule->Amount) {
                        $defaultAmount = $rule->Amount;
                    }
                    MonthlyAllowance::create([
                        'EmployeeID' => $emp->Id,
                        'AllowanceID' => $allowance->Id,
                        'Name' => $allowance->Name,
                        'Amount' => $defaultAmount,
                        'Month' => $month,
                        'Year' => $year,
                        'IsTaxable' => $allowance->IsTaxable,
                        'Status' => 'Pending',
                        'CreatedBy' => auth()->id(),
                        'CreatedOn' => now(),
                    ]);
                }
            }

            // Auto-create acting allowance (percentage of acting reference)
            if ($cycle) {
                $acting = EmployeeActingAssignment::where('EmployeeID', $emp->Id)
                    ->where('Status', 'Approved')
                    ->where(function($q) use ($cycle) {
                        $q->whereNull('EndDate')->orWhere('EndDate','>=', now()->startOfMonth()->setMonth($cycle->Month)->setYear($cycle->Year));
                    })
                    ->where('StartDate','<=', now()->endOfMonth()->setMonth($cycle->Month)->setYear($cycle->Year))
                    ->first();
                if ($acting) {
                    $actingAllowance = PayrollAllowance::with(['rules' => function($q){
                        $q->where('IsActive',1)->orderByDesc('EffectiveFrom');
                    }])->where('Code','ACTING')->first();
                    if ($actingAllowance) {
                        $ruleRate = optional($actingAllowance->rules->first())->Rate ?? ($acting->ActingAllowanceRate ?? 0);
                        $exists = MonthlyAllowance::where('EmployeeID', $emp->Id)
                            ->where('AllowanceID', $actingAllowance->Id)
                            ->where('Month', $cycle->Month)
                            ->where('Year', $cycle->Year)
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
                                'Month' => $cycle->Month,
                                'Year' => $cycle->Year,
                                'IsTaxable' => $actingAllowance->IsTaxable,
                                'Status' => 'Pending',
                                'CreatedBy' => auth()->id(),
                                'CreatedOn' => now(),
                            ]);
                        }
                    }
                }
            }
        }

        return redirect()->route('hr.payroll.runs.show', $run->Id)->with('success', 'Payroll run generated (placeholder lines created).');
    }

    public function show($id)
    {
        $run = PayrollRun::with(['cycle','lines.employee'])->findOrFail($id);
        return view('hr.payroll.runs.show', compact('run'));
    }
}
