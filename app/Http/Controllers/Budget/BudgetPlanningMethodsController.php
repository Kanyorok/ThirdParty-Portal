<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Budget\BudgetPlanningMethods;
use App\Enums\Core\PermissionEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BudgetPlanningMethodsController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetPlanningMethods::class);
        $methods = BudgetPlanningMethods::all();
        return view('budgetandanalytics.settings.planningmethods.index', compact('methods'));
    }

    public function create()
    {
        return view('budgetandanalytics.settings.planningmethods.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'MethodName' => 'required|string|max:150|unique:t_BudgetPlanningMethods,MethodName',
            'Description' => 'nullable|string',
            'IsActive' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $method = BudgetPlanningMethods::create([
                'MethodName' => $validated['MethodName'],
                'Description' => $validated['Description'] ?? null,
                'IsActive' => $validated['IsActive'] ?? false,
                'CreatedBy' => Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn(new BudgetPlanningMethods())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'Create'])
                ->log('Create Planning Methods');

            return redirect()->route('planningmethods.index')->with('success', 'Budget Planning Method created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Failed to create Budget Planning Method: ' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to create budget planning method.'])->withInput();
        }
    }

    public function edit($id)
    {

        $method = BudgetPlanningMethods::find($id);
        return view('budgetandanalytics.settings.planningmethods.edit', compact('method'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::BudgetSetupUpdate, BudgetPlanningMethods::class);

        $validated = $request->validate([
            'MethodName' => 'required|string|max:150|unique:t_BudgetPlanningMethods,MethodName',
            'Description' => 'nullable|string',
            'IsActive' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $method = BudgetPlanningMethods::findOrFail($id);

            $method->update([
                'MethodName' => $validated['MethodName'],
                'Description' => $validated['Description'] ?? null,
                'IsActive' => $validated['IsActive'] ?? false,
                'CreatedBy' => Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();

            activity()
                ->performedOn($method)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Planning Method');

            return redirect()->route('planningmethods.index')->with('success', 'Planning Method Updated Successfully');
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Failed to update Planning Method:' . $th->getMessage());

            return back()->withErrors(['Errors' => 'Failed to Update Planning Method'])->withInput();
        }
    }

    public function destroy(string $id)
    {
        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetPlanningMethods::class);
        try {
            $method = BudgetPlanningMethods::find($id)->delete();
            //$method->delete();

            activity()
                ->performedOn(new BudgetPlanningMethods())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'Delete'])
                ->log('Deleted Planning Method Successfully:' . $id);

            return redirect()->route('planningmethods.index')->with('Success', 'Planning Method Deleted Successfully');
        } catch (\Throwable $th) {
            Log::error('---DELETE PLANNING METHOD ERROR---' . $th->getMessage());
            return redirect()->route('planningmethods.index')->with('error', 'Failed to delete Planning Method. Please try again.');
        }
    }

}
