<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetGLAccount;
use App\Models\Budget\BudgetLine;
use App\Models\Budget\BudgetLineCategories;
use App\Models\Budget\BudgetLinesGLAccount;
use App\Models\Budget\BudgetProductType;
use App\Models\Core\CodeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Calculation\Category;

class BudgetLineMappingController extends Controller
{
    //
    public function index()
    {
        //Check if the user has permission to view using the enum set for budgetSetup
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetLine::class);

        //Fetch Budget Lines with related glAccounts and category
        $budgetLines = BudgetLine::with(['glAccounts', 'category'])->get();

        //Fetch Budget line category
        $budgetCategories=BudgetLineCategories::all();

        //Pull the GLS 
        $gls=BudgetGLAccount::select('Id','Description','GTType')->get();
        //Fetch Product type 
        $productTypes=BudgetProductType::select('Id','Name')->get();
        return view('budgetandanalytics.budgetlinemapping.index',compact('budgetLines','gls','productTypes','budgetCategories'));
    }

    public function create()
    {
        //Check Permissions
        $this->authorize(PermissionEnum::BudgetSetupCreate,BudgetLine::class);
        //Pull the GLS 
        $gls=BudgetGLAccount::select('Id','Description','GTType')->get();
        
        //Fetch Budget line category
        $budgetCategories=BudgetLineCategories::all();

        //Fetch Product type 
        $productTypes=BudgetProductType::select('Id','Name')->get();

        //Fetch GL Account Types
        $glAccountTypes=CodeDetail::where('CodeID','GLAccountType')->get();
        return view('budgetandanalytics.budgetlinemapping.create',compact(
            'gls',
            'productTypes',
            'budgetCategories',
            'glAccountTypes',
        ));
    }

    public function store(Request $request)
    {
        //Check Permissions
        $this->authorize(PermissionEnum::BudgetSetupCreate,BudgetLine::class);
        $validated = $request->validate([
            'BudgetLineCategoryID' => 'required|exists:t_BudgetLineCategories,Id',
            'LineName' => 'required|string|max:255',
            'Description' => 'required|string',
            'GLS' => 'required|array|min:1',
            'GLS.*' => 'required|integer|exists:t_BudgetGLAccounts,Id',
            // Add other fields and validation rules as needed
        ]);

        try {
            DB::beginTransaction();
            //Check if there is a default so that we can drop the existing and add the one created
            if($request->IsDefault){
                BudgetLine::where('IsDefault',1)->update(['IsDefault'=>0]);
            }
            $budgetLine = BudgetLine::create([
                'BudgetLineCategoryID' => $validated['BudgetLineCategoryID'],
                'LineName'=>$validated['LineName'],
                'Description'=>$validated['Description'],
                'IsDefault'   => $request->has('IsDefault') ? 1 : 0,

                'CreatedBy'=>Auth::id(),
                'ModifiedBy'=>Auth::id()
            ]);
            //Store the mappings in BudgetLinesGLAccount
            foreach ($validated['GLS'] as $glId) {
                if (!$glId) continue;
                BudgetLinesGLAccount::create([
                    'BudgetLineID' => $budgetLine->Id,
                    'BudgetGLAccountID' => $glId,

                    'CreatedBy' => Auth::id(),
                    'ModifiedBy' => Auth::id()
                ]);
            }

            DB::commit();
            //LOG Activity
            activity()
                ->performedOn($budgetLine)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Create a budget line mapping');

            //return back()->with('success', 'Budget Line Mapping created successfully.');
            return redirect()->route('budgetlinemapping.index') ->with('success', 'Budget Line Mapping created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th->getMessage();
            Log::error('Failed to store budget line mapping.', [
                'error' => $th->getMessage(),
                'stack' => $th->getTraceAsString()
            ]);
            return back()->with('error','An Error Occurred. Please try again');
        }

    }


public function update(Request $request, $id)
{
    // Check Permissions
    $this->authorize(PermissionEnum::BudgetSetupUpdate, BudgetLine::class);

    $validated = $request->validate([
        'BudgetLineCategoryID' => 'required|exists:t_BudgetLineCategories,Id',
        'LineName' => 'required|string|max:255',
        'Description' => 'required|string',
        'GLS' => 'required|array|min:1',
        'GLS.*' => 'required|integer|exists:t_BudgetGLAccounts,Id',
    ]);

    try {
        DB::beginTransaction();

        // Find the budget line
        $budgetLine = BudgetLine::findOrFail($id);

        // If marked as default, unset others
        if ($request->IsDefault) {
            BudgetLine::where('IsDefault', 1)->where('Id', '!=', $budgetLine->Id)->update(['IsDefault' => 0]);
        }

        // Update BudgetLine fields
        $budgetLine->update([
            'BudgetLineCategoryID' => $validated['BudgetLineCategoryID'],
            'LineName' => $validated['LineName'],
            'Description' => $validated['Description'],
            'IsDefault' => $request->has('IsDefault') ? 1 : 0,
            'ModifiedBy' => Auth::id()
        ]);

        // Sync GLS mappings
        $newGLIds = $validated['GLS'];

        // Get current mappings
        $existingGLIds = BudgetLinesGLAccount::where('BudgetLineID', $budgetLine->Id)->pluck('BudgetGLAccountID')->toArray();

        // Delete removed GLs
        $glsToDelete = array_diff($existingGLIds, $newGLIds);
        if (!empty($glsToDelete)) {
            BudgetLinesGLAccount::where('BudgetLineID', $budgetLine->Id)
                ->whereIn('BudgetGLAccountID', $glsToDelete)
                ->delete();
        }

        // Add new GLs
        $glsToAdd = array_diff($newGLIds, $existingGLIds);
        foreach ($glsToAdd as $glId) {
            BudgetLinesGLAccount::create([
                'BudgetLineID' => $budgetLine->Id,
                'BudgetGLAccountID' => $glId,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id()
            ]);
        }

        DB::commit();

        // LOG Activity
        activity()
            ->performedOn($budgetLine)
            ->causedBy(Auth::user())
            ->withProperties(['action' => 'update'])
            ->log('Updated a budget line mapping');

        return redirect()->route('budgetlinemapping.index')->with('success', 'Budget Line Mapping updated successfully.');

    } catch (\Throwable $th) {
        DB::rollBack();

        Log::error('Failed to update budget line mapping.', [
            'error' => $th->getMessage(),
            'stack' => $th->getTraceAsString()
        ]);

        return back()->with('error', 'An error occurred while updating. Please try again.');
    }
}



    public function destroy($id){
        //Check if user has permissions
        $this->authorize(PermissionEnum::BudgetSetupDelete,BudgetLine::class);
        //Try deleting the Line and its associated mappings in BudgetLinesGLAccount
        DB::beginTransaction();
        try {
            // Find the budget line
            $budgetLine = BudgetLine::findOrFail($id);

            // Delete associated mappings
            BudgetLinesGLAccount::where('BudgetLineID', $budgetLine->id)->delete();

            // Delete the budget line itself
            $budgetLine->delete();

            DB::commit();
            //Log the acitivity 
            activity()
                ->performedOn($budgetLine)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Delete a budget line mapping');
            return redirect()->back()->with('success', 'Budget Line and its mappings deleted successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Failed to delete Budget Line: ' . $th->getMessage());

            return redirect()->back()->with('error', 'Failed to delete Budget Line.');
        }
    }
}
