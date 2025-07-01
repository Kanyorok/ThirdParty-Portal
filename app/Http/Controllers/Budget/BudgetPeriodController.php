<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\Budget;
use App\Models\Budget\BudgetPeriods;
use App\Models\Budget\BudgetPeriodTypes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class BudgetPeriodController extends Controller
{
    //
    public function index()
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetPeriods::class);

        $budgets = Budget::all()->map(function ($budget) {
            $today = Carbon::today();
            $from = $budget->From ? Carbon::parse($budget->From) : null;
            $to = $budget->To ? Carbon::parse($budget->To) : null;

            $status = 'Unknown';
            $badgeClass = 'secondary';

            if ($from && $to) {
                if ($today->between($from, $to)) {
                    $status = 'Open';
                    $badgeClass = 'success';
                } elseif ($today->lt($from)) {
                    $status = 'Upcoming';
                    $badgeClass = 'info';
                } elseif ($today->gt($to)) {
                    $status = 'Expired';
                    $badgeClass = 'danger';
                }
            } elseif ($to && $today->gt($to)) {
                $status = 'Expired';
                $badgeClass = 'danger';
            } elseif ($from && $today->lt($from)) {
                $status = 'Upcoming';
                $badgeClass = 'info';
            }

            // Append computed values
            $budget->status = $status;
            $budget->badgeClass = $badgeClass;

            return $budget;
        });

        return view('budgetandanalytics.budgetperiod.index', compact('budgets'));
    }

    public function create()
    {
        $types = BudgetPeriodTypes::all();
        return view('budgetandanalytics.budgetperiod.create', compact('types'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'Name' => 'required|string|max:255',
            'FiscalYear' => 'required|integer|min:2020|max:2100',
            'From' => 'required|date',
            'To' => 'required|date|after_or_equal:From',
            'Notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $budget = Budget::create([
                'Name' => $validated['Name'],
                'FiscalYear' => $validated['FiscalYear'],
                'From' => $validated['From'], // ensure your column names match this
                'To' => $validated['To'],
                'Notes' => $validated['Notes'] ?? null,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
            ]);

            DB::commit();

            activity()
                ->performedOn($budget)
                ->causedBy(Auth::user())
                ->event('create')
                ->withProperties(['action' => 'create'])
                ->log('Created a budget');

            return redirect()->route('budgetperiod.index')->with('success', 'Budget created successfully.');

        } catch (Throwable $th) {
            DB::rollBack();
            Log::error('Failed to create budget: ' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to create Budget'])->withInput();
        }
    }

    public function edit($id)
    {
        $periods = BudgetPeriods::findOrFail($id);
        $types = BudgetPeriodTypes::all();
        return view('budgetandanalytics.budgetperiod.edit', compact('periods', 'types'));
    }

    public function update(Request $request, $id)
    {

        $this->authorize(PermissionEnum::BudgetSetupUpdate, BudgetPeriods::class);

        $validated = $request->validate([
            'fiscalYear' => 'required|string|max:10',
            'periodType' => 'required|exists:t_BudgetPeriodTypes,Id',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $period = BudgetPeriods::findOrFail($id);

            $period->update([
                'fiscalYear' => $validated['fiscalYear'],
                'periodType' => $validated['periodType'],
                'notes' => $validated['notes'],
                'CreatedBy' => Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);


            activity()
                ->performedOn($period)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Period');
            DB::commit();
            return redirect()->route('budgetperiod.index')->with('success', 'Period updated successfully');
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update period:' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update period'])->withInput();
        }

    }

    public function destroy(string $id)
    {
        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetPeriods::class);
        try {
            $period = BudgetPeriods::findOrFail($id); // safer: throws 404 if not found
            $period->delete();

            activity()
                ->performedOn(new BudgetPeriods())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'Delete'])
                ->log('Deleted Period Successfully:' . $id);

            return redirect()->route('budgetperiod.index')->with('Success', 'Period Deleted Successfully');
        } catch (Throwable $th) {
            Log::error('---DELETE PERIOD ERROR---' . $th->getMessage());
            return redirect()->route('budgetperiod.index')->with('error', 'Failed to delete Period. Please try again.');
        }
    }

}
