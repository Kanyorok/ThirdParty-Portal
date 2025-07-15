<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetGLAccount;
use App\Models\Budget\BudgetLine;
use App\Models\Budget\BudgetLineCategories;
use App\Models\Budget\BudgetLinesGLAccount;
use App\Models\Budget\BudgetProduct;
use App\Models\Budget\BudgetProductType;
use App\Models\Core\CodeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Calculation\Category;
use App\Models\HRM\Department;
use App\Models\Budget\BudgetGLAccountSubType;
use App\Models\Budget\BudgetLineProductTypes;

class BudgetLineMappingController extends Controller
{
    //
    public function index()
    {
        //Check if the user has permission to view using the enum set for budgetSetup
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetLine::class);

        //Fetch Budget Lines with related glAccounts and category
        $budgetLines = BudgetLine::with(['glAccounts', 'category', 'glAccountSubType', 'department', 'glAccountType'])->get();

        //Fetch Budget line category
        $budgetCategories = BudgetLineCategories::all();

        //Pull the GLS
        $gls = BudgetGLAccount::select('Id', 'Description', 'GTType')->get();

        //Fetch Product type
        $productTypes = BudgetProductType::select('Id', 'Name')->get();
        return view('budgetandanalytics.budgetlinemapping.index', compact('budgetLines', 'gls', 'productTypes', 'budgetCategories'));
    }

    public function create()
    {
        //Check Permissions
        $this->authorize(PermissionEnum::BudgetSetupCreate, BudgetLine::class);
        //Pull the GLS
        $gls = BudgetGLAccount::select('Id', 'Description', 'GTType')->get();

        //Fetch Budget line category
        $budgetCategories = BudgetLineCategories::all();

        //Fetch Product type
        //$productTypes=BudgetProductType::select('Id','Name')->get();
        $productTypes = BudgetProduct::select('Id', 'Description')->get();

        //Fetch GL Account Types
        $glAccountTypes = CodeDetail::select('Id', 'CodeID', 'Value', 'Description')->where('CodeID', 'GLAccountType')->get();

        //Fetch GLAccountSubType
        $glSubtype = BudgetGLAccountSubType::select('Id', 'GLAccountTypeValue', 'GLAccountSubTypeName')->get();

        $departments = Department::all();
        return view('budgetandanalytics.budgetlinemapping.create', compact(
            'gls',
            'productTypes',
            'budgetCategories',
            'glAccountTypes',
            'departments',
            'glSubtype'
        ));
    }

    public function store(Request $request)
    {
        //Check Permissions
        $this->authorize(PermissionEnum::BudgetSetupCreate, BudgetLine::class);
        $validated = $request->validate([
            'BudgetLineCategoryID' => 'required|exists:t_BudgetLineCategories,Id',
            'LineName' => 'required|string|max:255',
            'DepartmentID' => 'required|exists:t_Departments,Id',
            'GLAccountTypeID' => 'required|string',
            'GLAccountSubTypeID' => 'required|integer',
            'Description' => 'required|string',
            'IsProductDriven' => 'required|boolean',
            'GLS' => 'required|array|min:1',
            'GLS.*' => 'required|integer',
            // Add other fields and validation rules as needed
        ]);

        try {
            DB::beginTransaction();
            //Check if there is a default so that we can drop the existing and add the one created
            if ($request->IsDefault) {
                BudgetLine::where('IsDefault', 1)->update(['IsDefault' => 0]);
            }
            $budgetLine = BudgetLine::create([
                'BudgetLineCategoryID' => $validated['BudgetLineCategoryID'],
                'LineName' => $validated['LineName'],
                'DepartmentID' => $validated['DepartmentID'],
                'GLAccountTypeID' => $validated['GLAccountTypeID'],
                'GLAccountSubTypeID' => $validated['GLAccountSubTypeID'],
                'Description' => $validated['Description'],
                'IsDefault' => $request->has('IsDefault') ? 1 : 0,
                'IsProductDriven' => $validated['IsProductDriven'],

                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id()
            ]);
            //Store the mappings in BudgetLinesGLAccount
            foreach ($validated['GLS'] as $glId) {
                if (!$glId) continue;
                BudgetLinesGLAccount::create([
                    'BudgetLineID' => $budgetLine->Id,
                    'BudgetGLAccountID' => $glId,

                    'CreatedBy' => Auth::id(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedBy' => Auth::id()
                ]);
            }
            // Product Types mapping (if any)
            if ($request->has('ProductTypes') && is_array($request->ProductTypes)) {
                foreach ($request->ProductTypes as $typeId) {
                    //store in BudgetlineproductType
                    BudgetLineProductTypes::create([
                        'BudgetLineId' => $budgetLine->Id,
                        'ProductTypeId' => $typeId,
                        'CreatedBy' => Auth::id(),
                        'ModifiedBy' => Auth::id()
                    ]);
                }
            }
            DB::commit();
            //LOG Activity
            activity()
                ->performedOn($budgetLine)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Create a budget line mapping');

            //return back()->with('success', 'Budget Line Mapping created successfully.');
            return redirect()->route('budgetlinemapping.index')->with('success', 'Budget Line Mapping created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th->getMessage();
            Log::error('Failed to store budget line mapping.', [
                'error' => $th->getMessage(),
                'stack' => $th->getTraceAsString()
            ]);
            return back()->with('error', 'An Error Occurred. Please try again');
        }

    }

    public function edit($id)
    {
        $budgetLine = BudgetLine::findOrFail($id);
        $budgetCategories = BudgetLineCategories::all();
        $gls = BudgetGLAccount::select('Id', 'Description')->get();
        $productTypes = BudgetProduct::select('Id', 'Description')->get();
        $departments = \App\Models\HRM\Department::all();
        $glAccountTypes = \App\Models\Core\CodeDetail::select('Id', 'CodeID', 'Value', 'Description')->where('CodeID', 'GLAccountType')->get();
        $glSubtype = \App\Models\Budget\BudgetGLAccountSubType::select('Id', 'GLAccountTypeValue', 'GLAccountSubTypeName')->get();

        // Get currently selected GLs for this budget line
        $selectedGLs = BudgetLinesGLAccount::where('BudgetLineID', $budgetLine->Id)
            ->pluck('BudgetGLAccountID')
            ->toArray();
        // Get currently selected Product Types for this budget line
        $selectedProductTypes = BudgetLineProductTypes::where('BudgetLineId', $budgetLine->Id)
            ->pluck('ProductTypeId')
            ->toArray();

        return view('budgetandanalytics.budgetlinemapping.edit', compact(
            'budgetLine',
            'budgetCategories',
            'gls',
            'selectedGLs',
            'productTypes',
            'selectedProductTypes',
            'departments',
            'glAccountTypes',
            'glSubtype'
        ));
    }

    public function update(Request $request, $id)
    {
        // Check Permissions
        $this->authorize(PermissionEnum::BudgetSetupUpdate, BudgetLine::class);

        $validated = $request->validate([
            'BudgetLineCategoryID' => 'required|exists:t_BudgetLineCategories,Id',
            'LineName' => 'required|string|max:255',
            'DepartmentID' => 'required|exists:t_Departments,Id',
            'GLAccountTypeID' => 'required|string',
            'GLAccountSubTypeID' => 'required|integer',
            'Description' => 'required|string',
            'IsProductDriven' => 'required|boolean',
            'GLS' => 'required|array|min:1',
            'GLS.*' => 'required|integer|exists:t_BudgetGLAccounts,Id',
            // Add other fields and validation rules as needed
        ]);

        try {
            DB::beginTransaction();

            $budgetLine = BudgetLine::findOrFail($id);

            // If marked as default, unset others
            if ($request->IsDefault) {
                BudgetLine::where('IsDefault', 1)->where('Id', '!=', $budgetLine->Id)->update(['IsDefault' => 0]);
            }

            // Update BudgetLine fields
            $budgetLine->update([
                'BudgetLineCategoryID' => $validated['BudgetLineCategoryID'],
                'LineName' => $validated['LineName'],
                'DepartmentID' => $validated['DepartmentID'],
                'GLAccountTypeID' => $validated['GLAccountTypeID'],
                'GLAccountSubTypeID' => $validated['GLAccountSubTypeID'],
                'Description' => $validated['Description'],
                'IsDefault' => $request->has('IsDefault') ? 1 : 0,
                'IsProductDriven' => $validated['IsProductDriven'],
                'ModifiedBy' => Auth::id()
            ]);

            // Sync GLS mappings
            $newGLIds = $validated['GLS'];
            $existingGLIds = BudgetLinesGLAccount::where('BudgetLineID', $budgetLine->Id)->pluck('BudgetGLAccountID')->toArray();
            $glsToDelete = array_diff($existingGLIds, $newGLIds);
            if (!empty($glsToDelete)) {
                BudgetLinesGLAccount::where('BudgetLineID', $budgetLine->Id)
                    ->whereIn('BudgetGLAccountID', $glsToDelete)
                    ->delete();
            }
            $glsToAdd = array_diff($newGLIds, $existingGLIds);
            foreach ($glsToAdd as $glId) {
                BudgetLinesGLAccount::create([
                    'BudgetLineID' => $budgetLine->Id,
                    'BudgetGLAccountID' => $glId,
                    'CreatedBy' => Auth::id(),
                    'ModifiedBy' => Auth::id()
                ]);
            }

            // Sync Product Types mapping
            // Remove old mappings
            $productTypes = BudgetLineProductTypes::where('BudgetLineId', $budgetLine->Id)->delete();
            // Add new mappings if any
            if ($request->has('ProductTypes') && is_array($request->ProductTypes)) {
                foreach ($request->ProductTypes as $typeId) {
                    BudgetLineProductTypes::create([
                        'BudgetLineId' => $budgetLine->Id,
                        'ProductTypeId' => $typeId,
                        'CreatedBy' => Auth::id(),
                        'ModifiedBy' => Auth::id()
                    ]);
                }
            }

            // LOG Activity
            activity()
                ->performedOn($budgetLine)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated a budget line mapping');

            DB::commit();

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

    public function getGLAccountSubTypes($typeId)
    {
        $subTypes = \App\Models\Budget\BudgetGLAccountSubType::where('GLAccountTypeValue', $typeId)->get();
        return response()->json($subTypes);
    }

    public function show($id)
    {
        // Check permission
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetLine::class);

        $data = [];
        $budgetLine = BudgetLine::findOrFail($id);
        $budgetLineName = $budgetLine->LineName;

        $products = BudgetLineProductTypes::where('BudgetLineId', $id)->with('product')
            ->get();

        // Eager load productTypes for the given budget line
        // $budgetLine = BudgetLine::with(['productTypes'])->where('Id',$id)->firstOrFail();
        return view('budgetandanalytics.budgetlinemapping.show', compact('products', 'budgetLineName'));
    }


    public function destroy(Request $request, $id)
    {
        //Check if user has permissions
        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetLine::class);

        //Try deleting the Line and its associated mappings in BudgetLinesGLAccount
        DB::beginTransaction();
        try {
            //find budgetline by id
            $budgetLine = BudgetLine::findOrFail($id);
            //Delete GLS assoc with the budget line
            BudgetLinesGLAccount::where('BudgetLineID', $budgetLine->Id)->update(['DeletedBy' => Auth::id()]);
            BudgetLinesGLAccount::where('BudgetLineID', $budgetLine->Id)->delete();
            //Delete the products associated with the budget line
            BudgetLineProductTypes::where('BudgetLineId', $budgetLine->Id)->update(['DeletedBy' => Auth::id()]);
            BudgetLineProductTypes::where('BudgetLineId', $budgetLine->Id)->delete();
            //Delete the budget line itself
            $budgetLine->update(['DeletedBy' => Auth::id()]);
            $budgetLine->delete();
            $budgetLine->save();

            //Log the acitivity
            activity()
                ->performedOn(new BudgetLine())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Delete a budget line mapping');
            DB::commit();
            return redirect()->back()->with('success', 'Budget Line and its mappings deleted successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Failed to delete Budget Line: ' . $th->getMessage());

            return redirect()->back()->with('error', 'Failed to delete Budget Line.');
        }
    }


    public function destroyProduct(Request $request, $id)
    {
        //Check if user has permissions
        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetLine::class);

        //Try deleting the Line and its associated mappings in BudgetLinesGLAccount
        DB::beginTransaction();
        try {
            $budgetLineProduct = BudgetLineProductTypes::where('Id', $id)->delete();
            //Log the acitivity
            activity()
                ->performedOn(new BudgetLineProductTypes())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Delete a product from BudgetLine');
            DB::commit();
            return redirect()->back()->with('success', 'Product Detached from BudgetLine successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th->getMessage();
            Log::error('Failed to delete BudgetLine Product: ' . $th->getMessage());

            return redirect()->back()->with('error', 'Failed to delete Budget Line.');
        }
    }
}
