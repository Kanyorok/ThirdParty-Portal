<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Budget\BudgetPeriodTypes;
use App\Enums\Core\PermissionEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BudgetPeriodTypesController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetPeriodTypes::class);
        $types = BudgetPeriodTypes::all();

        return view('budgetandanalytics.settings.periodtypes.index', compact('types'));
    }

    public function create()
    {
        return view('budgetandanalytics.settings.periodtypes.create');
    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::BudgetSetupCreate, BudgetPeriodTypes::class);
        $validated = $request->validate([
            'PeriodType' => 'required|string|max:50',
            'Code' => 'required|string|max:10|unique:t_BudgetPeriodTypes,Code',
            'IsActive' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $type = BudgetPeriodTypes::create([
                'PeriodType' => $validated['PeriodType'],
                'Code' => $validated['Code'],
                'IsActive' => $validated['IsActive'] ?? true,
                'CreatedBy' => Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn(new BudgetPeriodTypes())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('create period types');
            return redirect()->route('periodtypes.index')->with('success', 'Budget Period Type created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to create Budget Period Type: ' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to create Budget Period Type.'])->withInput();
        }
    }


    public function edit($id)
    {

        $type = BudgetPeriodTypes::find($id);
        return view('budgetandanalytics.settings.periodtypes.edit', compact('type'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::BudgetSetupUpdate, BudgetPeriodTypes::class);

        $validated = $request->validate([
            'PeriodType' => 'required|string|max:50',
            'Code' => 'required|string|max:10|unique:t_BudgetPeriodTypes,Code',
            'IsActive' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $type = BudgetPeriodTypes::findOrFail($id);

            $type->update([
                'PeriodType' => $validated['PeriodType'],
                'Code' => $validated['Code'],
                'IsActive' => $validated['IsActive'] ?? true,
                'CreatedBy' => Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();

            activity()
                ->performedOn($type)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Period Type');

            return redirect()->route('periodtypes.index')->with('success', 'Period Type Updated Successfully');
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Failed to update Period type:' . $th->getMessage());

            return back()->withErrors(['Errors' => 'Failed to Update Period Type'])->withInput();
        }
    }

    public function destroy(string $id)
    {
        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetPeriodTypes::class);
        try {
            $type = BudgetPeriodTypes::find($id)->delete();
            //$type->delete();

            activity()
                ->performedOn(new BudgetPeriodTypes())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'Delete'])
                ->log('Deleted Period Type Successfully:' . $id);

            return redirect()->route('periodtypes.index')->with('Success', 'Period Type Deleted Successfully');
        } catch (\Throwable $th) {
            Log::error('---DELETE PERIOD TYPE ERROR---' . $th->getMessage());
            return redirect()->route('periodtypes.index')->with('error', 'Failed to delete Period Type. Please try again.');
        }
    }

}
