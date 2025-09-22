<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Budget\Budget;
use App\Models\Budget\BudgetGLMaster;
use App\Models\Budget\BudgetGLMasterAllocations;
use App\Models\Budget\BudgetGLsAttachments;
use App\Models\Core\Branch;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class BudgetTopDownAllocationController extends Controller
{
    //
    public function index()
    {
        $budgets = Budget::all();//Test network drive
        $branches = Branch::all();

        //Read data for GL master
        // $glsMaster=BudgetGLMaster::select(
        //     'BudgetGLID',
        //     'AccountID',
        //     'Description',
        //     'GLAccountTypeID',
        //     'GLSubAccountTypeID'
        // )->get();

        return view('budgetandanalytics.budgetworkspace.topdown.index', compact(
            'budgets',
            'branches',
        //'glsMaster',
        ));
    }

    public function display(Request $request)
    {
        $validated = $request->validate([
            'BudgetID' => 'required|exists:t_Budgets,Id',
            'BranchID' => 'required',
        ]);
        $budgets = Budget::all();
        $branches = Branch::all();

        $budgetId = $validated['BudgetID'];
        $branchId = $validated['BranchID'];

        //Logic for All branches
        if ($validated['BranchID'] == 'all') {
            return $this->displayAllBranches($validated['BudgetID']);
        }

        $check = BudgetGLMasterAllocations::where('BudgetID', $budgetId)
            ->where('BranchID', $branchId)
            ->first();

        $budgetName = Budget::find($budgetId)->Name;
        $branchName = Branch::find($branchId)->Name;

        if ($check) { // records exist

            // Check if this budget is being edited by another user
            $isBeingEdited = BudgetGLMasterAllocations::where('BudgetID', $validated['BudgetID'])
                ->where('BranchID', $validated['BranchID'])
                ->where('IsBeingEdited', true)
                ->exists();
            if ($isBeingEdited) {
                // Get the user ID of the person currently editing
                $editingUserId = BudgetGLMasterAllocations::where('BudgetID', $validated['BudgetID'])
                    ->where('BranchID', $validated['BranchID'])
                    ->where('IsBeingEdited', true)
                    ->value('IsBeingEditedBy');
                // Get the name of the user who is currently editing
                $editingUserName = User::find($editingUserId)->Name ?? 'Unknown User';
                // Redirect back with an error message not the same user
                if ($editingUserId == Auth::id()) {
                    // If the current user is the one editing, allow them to proceed
                } else {
                    // If another user is editing, return an error message
                    Log::info("User $editingUserName is currently editing BudgetID: $budgetId, BranchID: $branchId");
                    return back()->with('error', "This General Ledger is currently being edited by $editingUserName. Please try again later.");
                }
            } else {
                // Set the IsBeingEdited flag to true for the current user
                BudgetGLMasterAllocations::where('BudgetID', $validated['BudgetID'])
                    ->where('BranchID', $validated['BranchID'])
                    ->update([
                        'IsBeingEdited' => true,
                        'IsBeingEditedBy' => Auth::id(),
                    ]);
            }

            $glsMaster = BudgetGLMasterAllocations::where('BudgetID', $budgetId)
                ->where('BranchID', $branchId)
                ->get();

//            $glsMaster = collect(DB::select("EXEC GetBudgetWorkspace :budgetId, :branchId", [
//                'budgetId' => $budgetId,
//                'branchId' => $branchId
//            ]));

            $isExisting = true;
            return view('budgetandanalytics.budgetworkspace.topdown.exist', compact(
                'budgets',
                'branches',
                'glsMaster',
                'isExisting',
                'budgetName',
                'branchName',
                'budgetId',
                'branchId'
            ));
        } else {
            $glsMaster = BudgetGLsAttachments::where('BudgetID', $budgetId)
                ->whereNull('DeletedOn')
                ->select('Id', 'AccountID', 'Description', 'GLAccountTypeID')
                ->get();
            $isExisting = false;
            return view('budgetandanalytics.budgetworkspace.topdown.create', compact(
                'budgets',
                'branches',
                'glsMaster',
                'isExisting',
                'budgetName',
                'branchName',
                'budgetId',
                'branchId'
            ));
        }
    }


    public function displayAllBranches($budgetID)
    {
        $budgetId = $budgetID;
        $budgetName = Budget::find($budgetId)->Name ?? 'Unknown Budget';
        $branchName = 'All Branches';

        $budgets = Budget::all();
        $branches = Branch::all();

        $allocations = BudgetGLMasterAllocations::where('BudgetID', $budgetId)
            ->get();

        if ($allocations->isEmpty()) {
            // No records found - return the 'create' view
            $glsMaster = BudgetGLsAttachments::where('BudgetID', $budgetId)
                ->whereNull('DeletedOn')
                ->select('Id', 'AccountID', 'Description', 'GLAccountTypeID')
                ->get();

            $isExisting = false;

            return view('budgetandanalytics.budgetworkspace.topdown.create', compact(
                'budgets',
                'branches',
                'glsMaster',
                'isExisting',
                'budgetName',
                'branchName',
                'budgetId'
            ));
        }

        // Fields to be summed
        $monthFields = [
            'Month1', 'Month2', 'Month3', 'Month4', 'Month5', 'Month6',
            'Month7', 'Month8', 'Month9', 'Month10', 'Month11', 'Month12', 'Total'
        ];

        // Group by AccountID and sum all monthly fields
        $glsMaster = $allocations->groupBy('AccountID')->map(function ($items) use ($monthFields) {
            $summed = $items->first()->replicate(); // base object

            foreach ($monthFields as $field) {
                $summed->{$field} = $items->sum(function ($item) use ($field) {
                    return floatval($item->{$field});
                });
            }

            return $summed;
        })->values(); // convert to Collection

        $isExisting = true;

        //Call from SP
        return $glsMaster = collect(DB::select("EXEC GetBudgetWorkspaceAllBranches :budgetId", [
            'budgetId' => $budgetId
        ]));


        return view('budgetandanalytics.budgetworkspace.topdown.all', compact(
            'budgets',
            'branches',
            'glsMaster',
            'isExisting',
            'budgetName',
            'branchName',
            'budgetId'
        ));
    }


    public function create(Request $request)
    {
        //Check if such data has been created
        $budgetId = $request->get('BudgetID');
        $branchId = $request->get('BranchID');

        $topDownData = BudgetGLMasterAllocations::where('BudgetID', $budgetId)
            ->where('BranchID', $branchId)
            ->first();
        if ($topDownData) {
            //Load existing data
            $glsMaster = BudgetGLMasterAllocations::where('BudgetID', $budgetId)
                ->where('BranchID', $branchId)
                ->get();
        } else {
            //Read data for GL masters with already prepopulated alloc
            $glsMaster = BudgetGLMaster::select(
                'BudgetGLID',
                'AccountID_CBS',
                'Description',
                'GLAccountTypeID_CBS',
                'GLSubAccountTypeID_CBS'
            )->get();
        }

        return view('budgetandanalytics.budgetworkspace.topdown.create', compact('glsMaster'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'budgetId' => 'required|exists:t_Budgets,Id',
            'branchId' => 'required|exists:t_Branches,Id',
            'monthly_allocations' => 'required|array',
            'gl_data' => 'required|array',
        ]);

        $userId = Auth::user()->Id;
        $now = Carbon::now();

        try {
            DB::beginTransaction();

            $monthly_allocations = $validated['monthly_allocations'];
            $gl_data = $validated['gl_data'];

            foreach ($monthly_allocations as $accountID => $months) {
                $monthData = [];
                $total = 0;

                // Loop through 12 months and clean values
                for ($i = 1; $i <= 12; $i++) {
                    $amountStr = isset($months[$i]) ? $months[$i] : '0';
                    $cleanAmount = str_replace(',', '', $amountStr ?: '0');
                    $amount = (float)$cleanAmount;
                    $monthData["Month{$i}"] = $amount;
                    $total += $amount;
                }

                $description = $gl_data[$accountID]['Description'] ?? null;
                $glAttachmentId = $gl_data[$accountID]['AttachID'] ?? null;
                $glType = $gl_data[$accountID]['GLAccountTypeID'] ?? 'NA';

                BudgetGLMasterAllocations::create([
                    'BudgetID' => $validated['budgetId'],
                    'BranchID' => $validated['branchId'],
                    'GLAttachmentID' => $glAttachmentId,
                    'AccountID' => $accountID,
                    'Description' => $description,
                    'GLAccountTypeID' => $glType,
                    'Total' => $total,
                    'CreatedBy' => $userId,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $userId,
                    'ModifiedOn' => $now,
                    'DeletedBy' => null,
                    'DeletedOn' => null,
                    ...$monthData,
                ]);
            }

            DB::commit();
            return redirect()->route('topdownallocation.index')
                ->with('success', 'GL Budget Allocations saved successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Budget allocation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while saving budget allocations. Please try again.');
        }
    }


    public function show($id)
    {
        return redirect()->route('topdownallocation.index');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'budgetId' => 'required|exists:t_Budgets,Id',
            'branchId' => 'required|exists:t_Branches,Id',
            'monthly_allocations' => 'required|array',
            'monthly_allocations.*' => 'array',
            'monthly_allocations.*.*' => 'nullable|string', // Allow strings like "1,000"
        ]);

        DB::beginTransaction();

        try {
            $userId = Auth::id();
            $now = Carbon::now();

            foreach ($validated['monthly_allocations'] as $accountID => $months) {
                $total = 0;
                $monthData = [];

                for ($i = 1; $i <= 12; $i++) {
                    $amountRaw = $months[$i] ?? '0';
                    $amount = (float)str_replace(',', '', $amountRaw);
                    $monthData["Month{$i}"] = $amount;
                    $total += $amount;
                }

                // Get GL data once
                $glAttachment = BudgetGLsAttachments::where('BudgetID', $validated['budgetId'])
                    ->where('AccountID', $accountID)
                    ->whereNull('DeletedOn')
                    ->first();

                BudgetGLMasterAllocations::updateOrCreate(
                    [
                        'BudgetID' => $validated['budgetId'],
                        'BranchID' => $validated['branchId'],
                        'AccountID' => $accountID,
                    ],
                    array_merge([
                        'Description' => $glAttachment->Description ?? null,
                        'GLAccountTypeID' => $glAttachment->GLAccountTypeID ?? null,
                        'Total' => $total,
                        'ModifiedBy' => $userId,
                        'ModifiedOn' => $now,
                    ], $monthData)
                );
            }

            // Clear editing flags
            BudgetGLMasterAllocations::where('BudgetID', $validated['budgetId'])
                ->where('BranchID', $validated['branchId'])
                ->update([
                    'IsBeingEdited' => false,
                    'IsBeingEditedBy' => null,
                    'ModifiedOn' => $now,
                ]);

            // Log activity
            activity()
                ->causedBy(Auth::user())
                ->event('update')
                ->withProperties([
                    'action' => 'update_allocations',
                    'BudgetID' => $validated['budgetId'],
                    'BranchID' => $validated['branchId'],
                ])
                ->log('Updated budget allocations');

            DB::commit();

            return redirect()->route('topdownallocation.index')
                ->with('success', 'Budget allocations updated successfully.');
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error('Budget Allocation Update Failed: ' . $th->getMessage());

            // Remove this: return $th->getMessage(); // Debug only

            return back()->withInput()->withErrors([
                'error' => 'An error occurred while updating budget allocations. Please try again.'
            ]);
        }
    }


}
