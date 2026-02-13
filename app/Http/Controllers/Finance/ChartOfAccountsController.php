<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Jobs\Finance\SyncNmbGeneralLedgersJob;
use App\Models\Auth\ModelRole;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Branch;
use App\Models\Core\Currency;
use App\Models\Finance\FinanceGLAccounts;
use App\Models\Finance\FinanceGLSyncRun;
use App\Models\Finance\FinanceGLSubAccountTypes;
use App\Models\Finance\FinanceGLTypeGroup;
use App\Models\Finance\FinanceSyncGLAccount;
use App\Models\Finance\GLBranch;
use App\Models\Finance\SegmentOrder;
use Couchbase\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ChartOfAccountsController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceCOAView, FinanceGLAccounts::class);

        // Build query with filters
        $query = FinanceGLAccounts::with([
            'typeGroup:Id,Description',
            'subAccount:Id,Description',
        ]);

        // Apply filters if provided
        if ($request->filled('gl_name')) {
            $query->where('GLName', 'like', '%' . $request->gl_name . '%');
        }

        if ($request->filled('gl_code')) {
            $query->where('GLCode', 'like', '%' . $request->gl_code . '%');
        }

        if ($request->filled('gl_type')) {
            $query->where('GLAccountTypeID', $request->gl_type);
        }

        if ($request->filled('gl_type_group')) {
            $query->where('GLTypeGroupID', $request->gl_type_group);
        }

        if ($request->filled('description')) {
            $query->where('Description', 'like', '%' . $request->description . '%');
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('IsActive', $request->status === 'active');
        }

        // Apply sorting
        $sortField = $request->sort_by ?? 'Id';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        // Paginate results
        $perPage = $request->per_page ?? 25;
        $charts = $query->paginate($perPage)->withQueryString();

        // Get filter options for dropdowns
        $glTypes = CodeDetail::where('CodeID', 'GLAccountType')
            ->pluck('Description', 'Value')
            ->toArray();

        $glTypeGroups = FinanceGLTypeGroup::pluck('Description', 'Id')
            ->toArray();

        // Keep if you still need segment order elsewhere in the view
        $glOrders = SegmentOrder::select('Id', 'SegmentType', 'Description')
            ->orderBy('Id')
            ->get();

        $allowThirdPartyPosting = filter_var(
            env('ALLOW_THIRD_PARTY_FINANCE_POSTING', false),
            FILTER_VALIDATE_BOOLEAN
        );

        return view('finance.chartofaccounts.chartofaccounts.index', compact(
            'charts',
            'glOrders',
            'glTypes',
            'glTypeGroups',
            'allowThirdPartyPosting'
        ));
    }

    public function glSync()
    {
        $this->authorize(PermissionEnum::FinanceCOAView, FinanceGLAccounts::class);

        $allowThirdPartyPosting = filter_var(
            env('ALLOW_THIRD_PARTY_FINANCE_POSTING', false),
            FILTER_VALIDATE_BOOLEAN
        );

        $syncCount = FinanceSyncGLAccount::count();
        $lastSyncedAt = FinanceSyncGLAccount::max('ModifiedOn');
        $activeSync = FinanceGLSyncRun::whereIn('Status', ['pending', 'running'])
            ->orderBy('Id', 'desc')
            ->first();
        $latestSync = FinanceGLSyncRun::orderBy('Id', 'desc')->first();

        return view('finance.chartofaccounts.chartofaccounts.gl_sync', compact(
            'allowThirdPartyPosting',
            'syncCount',
            'lastSyncedAt',
            'activeSync',
            'latestSync'
        ));
    }

    public function startThirdPartySync(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceCOAView, FinanceGLAccounts::class);
        $request->validate([
            'page_size' => 'nullable|integer|min:1|max:5000',
        ]);

        $allowThirdPartyPosting = filter_var(
            env('ALLOW_THIRD_PARTY_FINANCE_POSTING', false),
            FILTER_VALIDATE_BOOLEAN
        );

        if (!$allowThirdPartyPosting) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Third-party GL sync is disabled. Set ALLOW_THIRD_PARTY_FINANCE_POSTING=true to enable.',
                ], 422);
            }

            return back()->with('error', 'Third-party GL sync is disabled. Set ALLOW_THIRD_PARTY_FINANCE_POSTING=true to enable.');
        }

        $userId = Auth::id() ?? 1;
        $pageSize = max(1, min(5000, (int) $request->input('page_size', 1000)));

        $activeSync = FinanceGLSyncRun::whereIn('Status', ['pending', 'running'])
            ->orderBy('Id', 'desc')
            ->first();

        if ($activeSync) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A GL sync is already running. Please wait for it to complete.',
                    'syncRunId' => $activeSync->Id,
                ], 409);
            }

            return back()->with('error', 'A GL sync is already running. Please wait for it to complete.');
        }

        $now = now();
        $syncRun = FinanceGLSyncRun::create([
            'Source' => 'NIMBLE',
            'Status' => 'pending',
            'Message' => 'Sync queued.',
            'RecordsSynced' => 0,
            'RecordsFailed' => 0,
            'TotalRecords' => null,
            'CurrentPage' => 0,
            'PageSize' => $pageSize,
            'LastCursor' => null,
            'StartedAt' => $now,
            'CreatedBy' => $userId,
            'CreatedOn' => $now,
            'ModifiedBy' => $userId,
            'ModifiedOn' => $now,
        ]);

        $useQueueWorker = filter_var(
            env('FINANCE_GL_SYNC_USE_QUEUE_WORKER', false),
            FILTER_VALIDATE_BOOLEAN
        );

        if ($useQueueWorker) {
            SyncNmbGeneralLedgersJob::dispatch($syncRun->Id)->onConnection('database');
        } else {
            // Fallback for environments without a running queue worker.
            SyncNmbGeneralLedgersJob::dispatch($syncRun->Id)->afterResponse();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'GL sync started in background.',
                'syncRunId' => $syncRun->Id,
                'dispatchMode' => $useQueueWorker ? 'queue_worker' : 'after_response',
            ]);
        }

        return back()
            ->with('success', 'GL sync started in background.')
            ->with('active_sync_id', $syncRun->Id);
    }

    public function getThirdPartySyncProgress(int $syncRunId)
    {
        $this->authorize(PermissionEnum::FinanceCOAView, FinanceGLAccounts::class);

        $syncRun = FinanceGLSyncRun::find($syncRunId);
        if (! $syncRun) {
            return response()->json([
                'success' => false,
                'message' => 'Sync run not found.',
            ], 404);
        }

        $totalRecords = (int) ($syncRun->TotalRecords ?? 0);
        $syncedRecords = (int) ($syncRun->RecordsSynced ?? 0);
        $percentage = $totalRecords > 0
            ? min(100, round(($syncedRecords / $totalRecords) * 100, 1))
            : null;

        return response()->json([
            'success' => true,
            'id' => $syncRun->Id,
            'status' => $syncRun->Status,
            'message' => $syncRun->Message,
            'recordsSynced' => $syncedRecords,
            'recordsFailed' => (int) ($syncRun->RecordsFailed ?? 0),
            'totalRecords' => $totalRecords,
            'currentPage' => (int) ($syncRun->CurrentPage ?? 0),
            'pageSize' => (int) ($syncRun->PageSize ?? 1000),
            'lastCursor' => $syncRun->LastCursor,
            'percentage' => $percentage,
            'isComplete' => $syncRun->Status === 'completed',
            'isFailed' => $syncRun->Status === 'failed',
            'error' => $syncRun->SyncError,
            'completedAt' => optional($syncRun->CompletedAt)->toDateTimeString(),
            'startedAt' => optional($syncRun->StartedAt)->toDateTimeString(),
        ]);
    }

    public function create()
    {
        $accountTypes = CodeDetail::select('CodeID', 'Value', 'Description')->where('CodeID', 'GLAccountType')->get();
        $typeGroups = FinanceGLTypeGroup::select('Id', 'Description')->get();
        $subAccountTypes = FinanceGLSubAccountTypes::select('Id', 'Description', 'GLTypeGroupId')->get();
        $allGLAccounts = FinanceGLAccounts::select('Id', 'GLCode', 'GLName', 'GLSubAccountTypeID')->get();
        $currencies = Currency::select('Id', 'Code')->get();

        return view('finance.chartofaccounts.chartofaccounts.create', compact(
            'accountTypes',
            'typeGroups',
            'subAccountTypes',
            'allGLAccounts',
            'currencies'
        ));
    }

    public function getTypeGroups(Request $request)
    {
        $typeGroups = FinanceGLTypeGroup::select('Id', 'Description', 'SegmentValue')->where('GLAccountTypeID', $request->GLAccountTypeID)->get();

        return response()->json($typeGroups);
    }

    public function getSubAccountTypes(Request $request)
    {
        $subTypes = FinanceGLSubAccountTypes::where('GLTypeGroupId', $request->GLTypeGroupID)->get();

        return response()->json($subTypes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            //'GLCode'             => 'required|string',
            'GLName' => [
                'required',
                'string',
                'max:50',
                Rule::unique('t_FinanceGLAccounts', 'GLName')
                    ->where(fn ($q) => $q->whereNull('DeletedOn')), // ignore soft-deleted rows
            ],
            'GLAccountTypeID' => 'required',
            'GLTypeGroupID' => 'required|exists:t_FinanceGLTypeGroups,Id',
            'Currency' => 'required|exists:t_Currencies,Id',
            'GLSubAccountTypeID' => 'required|exists:t_FinanceGLSubAccountTypes,Id',
            'Description' => 'required|string|max:255',
            'IsActive' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $branchID = ModelRole::where('model_id', Auth::id())->pluck('BranchID')->first();
            $branchIDCode = Branch::find($branchID)->BranchID;

            //Get the type values TO  be used in creating an account code
            $GLAccountTypeValue = CodeDetail::where('CodeID', 'GLAccountType')->where('Value', $validated['GLAccountTypeID'])->pluck('DisplayOrder')->first();
            $GLTypeGroupIDValue = FinanceGLTypeGroup::where('Id', $validated['GLTypeGroupID'])->pluck('SegmentValue')->first();
            $GLSubAccountTypeIDValue = FinanceGLSubAccountTypes::where('Id', $validated['GLSubAccountTypeID'])->pluck('SegmentValue')->first();
            $GLDigits = SegmentOrder::where('SegmentType', 'GLDigits')->pluck('Description')->first();


            $charts = FinanceGLAccounts::create([
                //'GLCode'             => $validated['GLCode'],
                'GLName' => $validated['GLName'],
                'GLAccountTypeID' => $validated['GLAccountTypeID'],
                'GLTypeGroupID' => $validated['GLTypeGroupID'],
                'GLSubAccountTypeID' => $validated['GLSubAccountTypeID'],
                'BranchID' => $branchIDCode,
                'GLAccountTypeValue' => $GLAccountTypeValue,
                'GLTypeGroupIDValue' => $GLTypeGroupIDValue,
                'GLSubAccountTypeIDValue' => $GLSubAccountTypeIDValue,
                'GLDigits' => $GLDigits,
                //'ParentGLID'         => $validated['ParentGLID'] ?? null,
                'Description' => $validated['Description'],
                'CurrencyID' => $validated['Currency'],
                'CreatedBy' => Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            //Insert the GlCode for the created GL
            $GLCode = $this->insertGLCodeFor($charts->Id);

            //Insert and create GLs for all the branches in t_GLBranch
            $branches = Branch::select('Id', 'BranchID')->get();
            foreach ($branches as $branch) {
                //Insert into t_GLBranch if does not exists
                $check = GLBranch::where('BranchID', $branch->Id)->where('GLAccountID', $charts->Id)->exists();
                if ($check) {
                    continue;
                }
                GLBranch::create([
                    'BranchID' => $branch->Id,
                    'GLAccountID' => $charts->Id,
                    'GLCode' => $GLCode,
                    'GLAccountType' => $charts->GLAccountTypeID,
                    'IsActive' => 1,
                    'CreatedBy' => Auth::Id(),
                    'ModifiedBy' => Auth::Id(),

                ]);
            }

            //Code to ensure all other GL are assigned this during init setup. To be commented after setup.
            $gls = FinanceGLAccounts::select('Id', 'GLCode', 'GLAccountTypeID')->get();
            foreach ($branches as $branch) {
                foreach ($gls as $gl) {
                    //Insert into t_GLBranch if does not exists
                    $check = GLBranch::where('BranchID', $branch->Id)->where('GLAccountID', $gl->Id)->exists();
                    if ($check) {
                        continue;
                    }
                    GLBranch::create([
                        'BranchID' => $branch->Id,
                        'GLAccountID' => $gl->Id,
                        'GLCode' => $gl->GLCode,
                        'GLAccountType' => $gl->GLAccountTypeID,
                        'IsActive' => 1,
                        'CreatedBy' => Auth::Id(),
                        'ModifiedBy' => Auth::Id(),
                    ]);
                }
            }


            activity()
                ->performedOn($charts)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Created budget activity: ' . $charts->GLName);

            DB::commit();

            return redirect()->route('chartofaccounts.index')->with('success', 'General Ledger Account created Successfully.');
        } catch (QueryException $e) {
            if ($e->getCode() == '23000') { // SQL duplicate error
                Log::error('Duplicate GL Code: ' . $e->getMessage());

                return back()->with('error', 'A General Ledger Account with this GL Code already exists.');
            }
        } catch (\Throwable $th) {
            DB::rollBack();

            return $th->getMessage();
            Log::error('Failed to create General Ledger Account:' . $th->getMessage());

            return back()->with('error', 'Failed to create General Ledger Account:' . $th->getMessage());
        }
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::FinanceCOAUpdate, FinanceGLAccounts::class);
        $gl = FinanceGLAccounts::with('typeGroup:Id,Description', 'subAccount:Id,Description')->find($id);
        if (! $gl) {
            return redirect()->route('chartofaccounts.index')->with('error', 'GL Account not found.');
        }
        $accountTypes = CodeDetail::select('CodeID', 'Value', 'Description')->where('CodeID', 'GLAccountType')->get();
        $typeGroups = FinanceGLTypeGroup::select('Id', 'Description')->get();
        $subAccountTypes = FinanceGLSubAccountTypes::select('Id', 'Description', 'GLTypeGroupId')->get();
        $allGLAccounts = FinanceGLAccounts::select('Id', 'GLCode', 'GLName', 'GLSubAccountTypeID')->get();

        $typeID = $gl->GLAccountTypeID;
        $subTypeID = $gl->GLTypeGroupID;

        $currencies = Currency::select('Id', 'Code')->get();

        return view('finance.chartofaccounts.chartofaccounts.edit', compact(
            'accountTypes',
            'typeGroups',
            'subAccountTypes',
            'allGLAccounts',
            'gl',
            'typeID',
            'subTypeID',
            'currencies'
        ));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::FinanceCOAUpdate, FinanceGLAccounts::class);
        $validated = $request->validate([
            'GLName' => [
                'required',
                'string',
                'max:50',
                Rule::unique('t_FinanceGLAccounts', 'GLName')
                    ->ignore($id, 'Id')                        // just use $id directly
                    ->where(fn ($q) => $q->whereNull('DeletedOn')),
            ],
            'GLAccountTypeID' => 'required',
            'GLTypeGroupID' => 'required|exists:t_FinanceGLTypeGroups,Id',
            'Currency' => 'required|exists:t_Currencies,Id',
            'GLSubAccountTypeID' => 'required|exists:t_FinanceGLSubAccountTypes,Id',
            'Description' => 'required|string|max:255',
            'IsActive' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $gl = FinanceGLAccounts::findOrFail($id);

            //Get the type values TO  be used in creating an account code
            $GLAccountTypeValue = CodeDetail::where('CodeID', 'GLAccountType')->where('Value', $validated['GLAccountTypeID'])->pluck('DisplayOrder')->first();
            $GLTypeGroupIDValue = FinanceGLTypeGroup::where('Id', $validated['GLTypeGroupID'])->pluck('SegmentValue')->first();
            $GLSubAccountTypeIDValue = FinanceGLSubAccountTypes::where('Id', $validated['GLSubAccountTypeID'])->pluck('SegmentValue')->first();
            $GLDigits = SegmentOrder::where('SegmentType', 'GLDigits')->pluck('Description')->first();

            $gl = FinanceGLAccounts::where('Id', $id)->update([
                'GLName' => $validated['GLName'],
                'GLAccountTypeID' => $validated['GLAccountTypeID'],
                'GLTypeGroupID' => $validated['GLTypeGroupID'],
                'GLSubAccountTypeID' => $validated['GLSubAccountTypeID'],
                'GLAccountTypeValue' => $GLAccountTypeValue,
                'GLTypeGroupIDValue' => $GLTypeGroupIDValue,
                'GLSubAccountTypeIDValue' => $GLSubAccountTypeIDValue,
                'GLDigits' => $GLDigits,
                'CurrencyID' => $validated['Currency'],
                'Description' => $validated['Description'],
                'IsActive' => $validated['IsActive'],
                'ModifiedBy' => Auth::id(),
            ]);

            //Update the GlCode for the updated GL
            $GLCode = $this->insertGLCodeFor($id);

            activity()
                ->performedOn(new FinanceGLAccounts())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated GL account: ');

            DB::commit();

            return redirect()->route('chartofaccounts.index')->with('success', 'General Ledger Account updated successfully.');
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('Update error: ' . $e->getMessage());

            return back()->with('error', 'An error occurred while updating the GL account.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Unexpected error: ' . $th->getMessage());

            return back()->with('error', 'Failed to update the GL account: ' . $th->getMessage());
        }
    }

    public function hierarchy()
    {
        $accounts = FinanceSyncGLAccount::orderBy('GLCode')->get();

        return view('finance.chartofaccounts.chartofaccounts.account_hierarchy', compact('accounts'));
    }

    public function show($code)
    {
        $account = FinanceSyncGLAccount::where('GLCode', $code)->first();

        if (! $account) {
            abort(404, 'Account not found.');
        }

        return view('finance.chartofaccounts.chartofaccounts.show', compact('account'));
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::FinanceCOADelete, FinanceGLAccounts::class);

        try {
            DB::beginTransaction();
            $gl = FinanceGLAccounts::find($id);
            if (! $gl) {
                return back()->with('error', 'GL Account not found.');
            }
            $gl->DeletedBy = Auth::Id();
            $gl->DeletedOn = now();
            $gl->IsActive = 0;
            $gl->save();
            $gl->delete();

            activity()
                ->performedOn($gl)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated GL account: ' . $gl->GLName);
            DB::commit();

            return back()->with('success', 'GL Account deleted successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to delete GL Account:' . $th->getMessage());

            return back()->with('error', 'Failed to delete GL Account:' . $th->getMessage());
        }
    }

    public function insertGLCodeFor(int $glId): ?string
    {
        // 1) Get segment order once
        $segments = SegmentOrder::select('Id', 'SegmentType', 'Description')
            ->orderBy('Id')
            ->get();

        if ($segments->isEmpty()) {
            return null; // nothing to build with
        }

        // 2) Fetch just the one GL account
        /** @var \App\Models\FinanceGLAccount $glAccount */
        $glAccount = FinanceGLAccounts::find($glId);
        if (! $glAccount) {
            return null; // not found
        }

        // 3) Build parts following the same logic
        $parts = [];
        foreach ($segments as $segment) {
            if ($segment->SegmentType === 'GLDigits') {
                // Pad account Id to the digits specified in segment Description (fallback to account->GLDigits if present)
                $digits = (int)($segment->Description ?? $glAccount->GLDigits ?? 1);
                $digits = max($digits, 1);
                $parts[] = str_pad((string)$glAccount->Id, $digits, '0', STR_PAD_LEFT);
            } else {
                // Use SegmentType as column name on FinanceGLAccounts
                $column = $segment->SegmentType;               // e.g. BranchCode, Major, etc.
                $value = data_get($glAccount, $column, '');   // safe accessor
                $parts[] = (string)$value;
            }
        }

        // 4) Join with dashes; drop empty parts
        $parts = array_values(array_filter($parts, fn ($v) => $v !== null && $v !== ''));
        $glCode = implode('-', $parts);

        // 5) Save and return
        $glAccount->GLCode = $glCode;
        $glAccount->save();

        return $glCode;
    }
}
