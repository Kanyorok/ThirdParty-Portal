<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\Budget;
use App\Models\Budget\BudgetActivity;
use App\Models\Budget\BudgetGLMaster;
use App\Models\Budget\BudgetGLMasterAllocations;
use App\Models\Budget\BudgetGLsAttachments;
use App\Models\Budget\BudgetLine;
use App\Models\Budget\BudgetManualEntry;
use App\Models\Budget\BudgetProjection;
use App\Models\Budget\BudgetProjectionData;
use Illuminate\Http\Request;
use App\Models\Budget\BudgetPeriods;
use App\Models\Budget\BudgetPeriodTypes;
use App\Models\Budget\BudgetProductType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        // Fetch Budget Period Types (if still needed for the commented-out periodType dropdown)
        $types = BudgetPeriodTypes::all();

        // Fetch all GL Subtypes
        $glSubtypes = DB::table('t_BudgetGLSubTypes')
            ->select('Id', 'GLAccountTypeID', 'GLSubAccountTypeID', 'Description')
            ->get();

        // Fetch all GL Accounts with GLAccountTypeID
        $glAccounts = DB::table('t_BudgetGLMaster')
            ->select('AccountID', 'Description', 'GLSubAccountTypeID', 'GLAccountTypeID')
            ->get();

        return view('budgetandanalytics.budgetperiod.create', compact('types', 'glSubtypes', 'glAccounts'));
    }



    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::BudgetSetupCreate, BudgetLine::class);

         $validated = $request->validate([
            'Name' => 'required|string|max:255',
            'FiscalYear' => 'required|integer|min:2020|max:2100',
            'From' => 'required|date',
            'To' => 'required|date|after_or_equal:From',
            'Notes' => 'nullable|string|max:1000',
            'selected_gls' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $gls = json_decode($value, true);
                    if (!is_array($gls) || empty($gls)) {
                        $fail('At least one GL account must be selected.');
                    }
                },
            ],
        ]);
        DB::beginTransaction();
        try {
            // Create the budget
            $budget = Budget::create([
                'Name' => $validated['Name'],
                'FiscalYear' => $validated['FiscalYear'],
                'From' => $validated['From'],
                'To' => $validated['To'],
                'Notes' => $validated['Notes'] ?? null,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ]);

            // Handle selected GLs
            $selectedGls = json_decode($validated['selected_gls'], true);
            foreach ($selectedGls as $gl) {
                BudgetGLsAttachments::create([
                    'BudgetID' => $budget->Id,
                    'GLID' => $gl['AccountID'], // Assuming AccountID maps to BudgetGLID
                    'AccountID' => $gl['AccountID'],
                    'Description' => $gl['Description'] ?? null,
                    'GLAccountTypeID' => $gl['GLAccountTypeID'],
                    'CreatedBy' => Auth::id(),
                    'CreatedOn' => now(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);
            }


            activity()
                ->performedOn($budget)
                ->causedBy(Auth::user())
                ->event('create')
                ->withProperties(['action' => 'create'])
                ->log('Created a budget');

            DB::commit();
            return redirect()->route('budgetperiod.index')->with('success', 'Budget created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to create budget: ' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to create Budget'])->withInput();
        }
    }


    public function show($id)
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetPeriods::class);

        try {
            // Fetch the budget
            $budget = Budget::findOrFail($id);

            // Fetch attached GLs (excluding soft-deleted)
            $glAttachments = BudgetGLsAttachments::where('BudgetID', $id)
                ->whereNull('DeletedOn')
                ->get();

            // Fetch GL Account Types for description mapping
            $glAccountTypes = DB::table('t_BudgetGLSubTypes')
                ->select('GLAccountTypeID', 'Description')
                ->distinct()
                ->get();

            return view('budgetandanalytics.budgetperiod.show', compact('budget', 'glAttachments', 'glAccountTypes'));
        } catch (\Throwable $th) {
            Log::error('Failed to load budget: ' . $th->getMessage());
            return $th->getMessage();
            return redirect()->route('budgetperiod.index')->withErrors(['error' => 'Failed to load budget']);
        }
    }


    public function edit($id)
    {
        //I have commented code below so as to make this method only handle GL attachment
        // Fetch Budget Period Types (if still needed for the commented-out periodType dropdown)
        $types = BudgetPeriodTypes::all();

        // Fetch all GL Subtypes
        $glSubtypes = DB::table('t_BudgetGLSubTypes')
            ->select('Id', 'GLAccountTypeID', 'GLSubAccountTypeID', 'Description')
            ->get();

        // Fetch all GL Accounts with GLAccountTypeID and filter out the ones already attached
        //Get attached AccountIDs for this budget
        $attachedAccountIDs = BudgetGLsAttachments::where('BudgetID', $id)
                    ->pluck('AccountID')
                    ->toArray();

        //Get GL Accounts that are NOT attached
        $glAccounts = DB::table('t_BudgetGLMaster')
                    ->select('AccountID', 'Description', 'GLSubAccountTypeID', 'GLAccountTypeID')
                    ->whereNotIn('AccountID', $attachedAccountIDs)
                    ->get();
        $budget=Budget::find($id);
        return view('budgetandanalytics.budgetperiod.edit', compact('types', 'glSubtypes', 'glAccounts','budget'));

        //return$budgetGLAttachments=BudgetGLsAttachments::select('Id','BudgetID','AccountID','GLID','GLAccountTypeID','Description')->where('BudgetID',$id)->get();

//        $periods = BudgetPeriods::findOrFail($id);
//        $types = BudgetPeriodTypes::all();
//        return view('budgetandanalytics.budgetperiod.edit', compact('periods', 'types'));
    }


    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::BudgetSetupUpdate, BudgetPeriods::class);

        $validated = $request->validate([
            'Name'        => 'required|string|max:255',
            'FiscalYear'  => 'required|integer|min:2000|max:2100',
            'From'        => 'required|date',
            'To'          => 'required|date|after_or_equal:From',
            'Notes'       => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $period = Budget::findOrFail($id);

            $period->update([
                'Name'        => $validated['Name'],
                'FiscalYear'  => $validated['FiscalYear'],
                'From'        => $validated['From'],
                'To'          => $validated['To'],
                'Notes'       => $validated['Notes'] ?? null,
                'ModifiedBy'  => Auth::id(),
            ]);

            DB::commit();

            activity()
                ->performedOn($period)
                ->causedBy(Auth::id())
                ->withProperties(['action' => 'update'])
                ->log('Updated Budget Period');

            return redirect()->route('budgetperiod.index')->with('success', 'Budget updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to update budget period: ' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update budget'])->withInput();
        }
    }



    public function destroy(string $id)
    {
        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetPeriods::class);
        try{
            $period = BudgetPeriods::findOrFail($id); // safer: throws 404 if not found
            $period -> DeletedBy = Auth::Id();
            $period->delete();

            activity()
                ->performedOn(new BudgetPeriods())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'Delete'])
                ->log('Deleted Period Successfully:' . $id);

            return redirect()->route('budgetperiod.index')->with('Success', 'Period Deleted Successfully');
        } catch (\Throwable $th) {
            Log::error('---DELETE PERIOD ERROR---' . $th->getMessage());
            return redirect()->route('budgetperiod.index')->with('error', 'Failed to delete Period. Please try again.');
        }
    }


    public function delGLAttachment($id)
    {
        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetGLsAttachments::class);

        try {
            DB::beginTransaction();
            $glAttachment = BudgetGLsAttachments::find($id);
            $glAttachment->DeletedBy = Auth::Id();
            $glAttachment->delete();

            //Delete the associated BudgetGLMasterAllocations if they exist
            //
            $findAlloc=BudgetGLMasterAllocations::where('GLAttachmentID', $id)->first();
            if ($findAlloc) {
                $findAlloc->DeletedBy = Auth::id();
                $findAlloc->save(); // Save before deleting if you want DeletedBy recorded
                $findAlloc->delete();
            }

            //BudgetGLMasterAllocations::where('GLAttachmentID', $id)->delete();

            activity()
                ->performedOn($glAttachment)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Deleted GL Attachment Successfully: ' . $id);
            DB::commit();
            return back()->with('success', 'GL Attachment deleted successfully.');
            //return response()->json(['success' => true, 'message' => 'GL Attachment deleted successfully.']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th->getMessage();
            Log::error('Failed to delete GL Attachment: ' . $th->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete GL Attachment.'], 500);
        }
    }

    public function attachGL(Request $request)
    {
        $this->authorize(PermissionEnum::BudgetSetupCreate, BudgetLine::class);
        $validated = $request->validate([
            'selected_gls' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $gls = json_decode($value, true);
                    if (!is_array($gls) || empty($gls)) {
                        $fail('At least one GL account must be selected.');
                    }
                },
            ],
        ]);

        $budgetID=$request->budgetID;
        DB::beginTransaction();
        try {
            // Handle selected GLs
            $selectedGls = json_decode($validated['selected_gls'], true);
            foreach ($selectedGls as $gl) {
                $action=BudgetGLsAttachments::create([
                    'BudgetID' => $budgetID,
                    'GLID' => $gl['AccountID'], // Assuming AccountID maps to BudgetGLID
                    'AccountID' => $gl['AccountID'],
                    'Description' => $gl['Description'] ?? null,
                    'GLAccountTypeID' => $gl['GLAccountTypeID'],
                    'CreatedBy' => Auth::id(),
                    'CreatedOn' => now(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);

                //Check if the attachments have already been placed to the BudgetGLAllocation table
                $check=BudgetGLMasterAllocations::where('BudgetID',$budgetID)->first();

                $now = Carbon::now();
                if($check){// Do some insert in the BudgetMasterAllocation
                    //Get distinct values of all the branches id
                    $branchIDS=BudgetGLMasterAllocations::where('BudgetID',$budgetID)->distinct()->pluck('BranchID')->toArray();
                    //Insert the data based on the branches
                    foreach($branchIDS as $branchID){
                        BudgetGLMasterAllocations::create([
                            'BudgetID' => $budgetID,
                            'BranchID' => $branchID,
                            'GLAttachmentID' => $action->Id,
                            'AccountID' => $gl['AccountID'],
                            'Description' => $gl['Description'] ?? null,
                            'GLAccountTypeID' =>  $gl['GLAccountTypeID'],
                            //'Total' => $total,
                            'CreatedBy' => Auth::id(),
                            'CreatedOn' => $now,
                            'ModifiedBy' => Auth::id(),
                            'ModifiedOn' => $now,
                            'DeletedBy' => null,
                            'DeletedOn' => null,
                            //...$monthData,
                        ]);
                    }
                }
            }


            activity()
                ->performedOn($action)
                ->causedBy(Auth::user())
                ->event('create')
                ->withProperties(['action' => 'create'])
                ->log('Created a budget');

            DB::commit();
            return redirect()->route('budgetperiod.index')->with('success', 'GL Attached Successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th->getMessage();
            Log::error('Failed to attach GLs: ' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to create Budget'])->withInput();
        }
    }

    public function delBudget($id){

        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetLine::class);

        try{
            DB::beginTransaction();

            //Delete Activities assoc
            $activity=BudgetActivity::where('BudgetID',$id)->update(['DeletedBy' => Auth::id(), 'DeletedOn' => now()]);
            $activity=BudgetActivity::where('BudgetID',$id)->delete();

            //Delete Lines Assoc
            $line=BudgetManualEntry::where('BudgetID',$id)->update(['DeletedBy' => Auth::id(), 'DeletedOn' => now()]);
            $line=BudgetManualEntry::where('BudgetID',$id)->delete();

            //Deleting Projections
            //Delete All Projections for that Budget
            $budgetProjection=BudgetProjection::where('BudgetID', $id)->update(['DeletedBy'=>Auth::id()]);
            $budgetProjectionData=$budgetProjection;
            BudgetProjection::where('BudgetID', $id)->delete();
            //Delete All allocation related to that Budget Id
            BudgetProjectionData::where('BudgetID',$id)->update(['DeletedBy'=>Auth::id()]);
            BudgetProjectionData::where('BudgetID',$id)->delete();


            //Deleting the budget
            $budget=Budget::find($id);
            $budgetLog=$budget;
            $budget->DeletedBy=Auth::id();
            $budget->delete();
            $budget->save();

            //Log activity
            activity()
                ->performedOn($budgetLog)
                ->causedBy(Auth::id())
                ->event('delete')
                ->withProperties(['action' => 'delete'])
                ->log('Deleted a budget');
            DB::commit();
            return back()->with('success', 'Budget Deleted Successfully.');
        }catch(\Throwable $th){
            DB::rollBack();

            Log::error('Failed to delete Budget: ' . $th->getMessage());
            return back()->withErrors(['error' => 'Failed to delete Budget. Please try again.']);
        }
    }
}
