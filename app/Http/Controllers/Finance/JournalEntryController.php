<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\Finance\FinanceGLAccounts;
use App\Models\Finance\FinanceJournalEntry;
use App\Models\Finance\FinanceJournalLines;
use App\Models\HRM\Department;
use App\Services\Workflow\ApprovalWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JournalEntryController extends Controller
{
    protected $workflowService;

    public function __construct(ApprovalWorkflow $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    public function index(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerView, FinanceJournalEntry::class);

        // Build query with filters
        $query = FinanceJournalEntry::with('journalLines:Id,JournalEntryId,Debit,Credit,Amount,IsDebit,Narration')
            ->select('Id', 'RefNo', 'Date', 'Description', 'ApprovalStatus', 'Status', 'Type', 'SourceModule', 'IsReversed')
            ->where('Type', 'normal');

        // Apply filters if provided
        if ($request->filled('ref_no')) {
            $query->where('RefNo', 'like', '%' . $request->ref_no . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('Date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('Date', '<=', $request->date_to);
        }

        if ($request->filled('description')) {
            $query->where('Description', 'like', '%' . $request->description . '%');
        }

        if ($request->filled('approval_status') && $request->approval_status !== 'all') {
            $query->where('ApprovalStatus', $request->approval_status);
        }

        // Apply sorting
        $sortField = $request->sort_by ?? 'Date';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        // Paginate results
        $perPage = $request->per_page ?? 10;
        $journalEntries = $query->paginate($perPage)->withQueryString();

        // Get filter options for dropdowns
        $approvalStatuses = FinanceJournalEntry::distinct()
            ->where('Type', 'normal')
            ->pluck('ApprovalStatus')
            ->filter()
            ->unique()
            ->values();

        return view('finance.generalledger.journalentry.index', compact(
            'journalEntries',
            'approvalStatuses'
        ));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerCreate, FinanceJournalEntry::class);
        $gls = FinanceGLAccounts::select('Id', 'GLName', 'GLCode')->get();
        $branches = Branch::select('Id', 'Name')->get();
        $departments = Department::select('Id', 'Name')->get();

        return view('finance.generalledger.journalentry.create', compact('gls', 'branches', 'departments'));
    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerCreate, FinanceJournalEntry::class);
        // Reconstruct the entries array to help in checking if CR wquals DR
        $entries = [];
        foreach ($request->GLAccount as $index => $gl) {
            $entries[] = [
                'gl_id' => $gl,
                'branch_id' => $request->Branch[$index],
                'department_id' => $request->Department[$index],
                'debit' => $request->DRCR[$index] === 'DR' ? $request->Amount[$index] : 0,
                'credit' => $request->DRCR[$index] === 'CR' ? $request->Amount[$index] : 0,
                'is_debit' => $request->DRCR[$index] === 'DR' ? true : false,
                'amount' => $request->Amount[$index],
                'narration' => $request->Narration[$index] ?? null,
            ];
        }

        // Merge the normalized structure into the request
        $request->merge(['entries' => $entries]);

        // Now validate
        $validated = $request->validate([
            'JournalDate' => 'required|date',
            'Description' => 'nullable|string',
            'entries' => 'required|array|min:2',
            'entries.*.gl_id' => 'required|exists:t_FinanceGLAccounts,Id',
            'entries.*.branch_id' => 'required|exists:t_Branches,Id',
            'entries.*.department_id' => 'required|exists:t_Departments,Id',
            'entries.*.debit' => 'required|numeric|min:0',
            'entries.*.credit' => 'required|numeric|min:0',
        ]);

        // Check if total DR equals total CR
        $totalDebit = collect($validated['entries'])->sum('debit');
        $totalCredit = collect($validated['entries'])->sum('credit');

        if ($totalDebit != $totalCredit) {
            return back()->withErrors(['Amount mismatch' => 'Total Debit must equal Total Credit'])->withInput();
        }

        DB::beginTransaction();

        try {
            // Save the master entry in the JournalEntry Table
            $journalEntry = FinanceJournalEntry::create([
                'Date' => $request->JournalDate,
                'Description' => $request->Description,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            // SAVE to the JournalLines table
            foreach ($request->entries as $entry) {
                FinanceJournalLines::create([
                    'JournalEntryId' => $journalEntry->Id,
                    'GLAccountID' => $entry['gl_id'],
                    'BranchID' => $entry['branch_id'],
                    'DepartmentID' => $entry['department_id'],
                    'IsDebit' => $entry['is_debit'],
                    'Amount' => $entry['is_debit'] ? $entry['amount'] * -1 : $entry['amount'],
                    'Debit' => ($entry['debit'] * -1) ?? 0,
                    'Credit' => $entry['credit'] ?? 0,
                    'Narration' => $entry['narration'] ?? null,
                    'CreatedBy' => Auth::id(),
                    'ModifiedBy' => Auth::Id(),
                ]);
            }
            activity('Journal Entry Creation')
                ->performedOn(new FinanceJournalEntry())
                ->causedBy(Auth::id())
                ->withProperties(['Create' => $journalEntry])
                ->log('Created Journal Entry');
            DB::commit();

            return back()->with('success', "Journal Entry ($journalEntry->RefNo) created successfully.");
            //return redirect()->route('journalentry.index')->with('success', 'Journal Entry created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            //return $th->getMessage();
            Log::error('Failed to create Journal Entry' . $th->getMessage());

            return back()->with('error', 'Failed to create Journal Entry');
        }
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerView, FinanceJournalEntry::class);
        $journalEntry = FinanceJournalEntry::with([
            'journalLines.glAccount',
            'sourceModule',
            'createdBy:Id,Name',
            'modifiedBy:Id,Name',
            'reversalsAsOriginal' => function ($query) {
                $query->with('journalEntry.createdBy:Id,Name');
            },
        ])->findOrFail($id);

        // Check if user can approve (pending row + stage permission + maker-checker)
        try {
            $canApprove = $this->workflowService->canApproveModel($journalEntry, Auth::user());
            Log::info("Can approve check completed", ['journal_entry_id' => $id, 'can_approve' => $canApprove]);
        } catch (\Exception $e) {
            Log::warning("Failed to check approval permission", [
                'journal_entry_id' => $id,
                'error' => $e->getMessage(),
            ]);
            $canApprove = false;
        }

        $workflowSources = array_values(array_unique(array_filter([
            $journalEntry->getTable(),
            $journalEntry->getMorphClass(),
            $journalEntry::getPrimaryKey(),
        ])));

        $hasPendingApprovals = DB::table('t_WorkFlowPending')
            ->whereIn('Source', $workflowSources)
            ->where('SourceID', (string)$journalEntry->getKey())
            ->whereNull('DeletedOn')
            ->exists();

        $cantApproveReason = null;
        if ($hasPendingApprovals && ! $canApprove) {
            $cantApproveReason = $this->getCantApproveReason(
                $workflowSources,
                $journalEntry->getKey(),
                Auth::user()
            );
        }

        $permissionTable = config('permission.table_names.permissions', 't_Permissions');
        $rolesTable = config('permission.table_names.roles', 't_Roles');
        $modelRolesTable = config('permission.table_names.model_has_roles', 't_ModelRoles');
        $rolePivotKey = config('permission.column_names.role_pivot_key') ?? 'role_id';
        $modelMorphKey = config('permission.column_names.model_morph_key', 'model_id');

        $workflowHistory = DB::table('t_WorkFlowHistory as h')
            ->leftJoin('t_Users as u', 'h.CreatedBy', '=', 'u.Id')
            ->leftJoin('t_CodeDetails as cd', 'h.StatusId', '=', 'cd.ID')
            ->leftJoin('t_WorkFlowStages as ws', 'h.Stage', '=', 'ws.Id')
            ->leftJoin($permissionTable . ' as perm', 'ws.PermissionId', '=', 'perm.id')
            ->whereIn('h.Source', $workflowSources)
            ->where('h.SourceID', (string)$journalEntry->getKey())
            ->whereNull('h.DeletedOn')
            ->orderBy('h.CreatedOn')
            ->get([
                'h.CreatedOn',
                'h.Notes',
                'u.Name as UserName',
                'cd.Description as StatusDescription',
                'ws.StageName',
                'perm.name as PermissionName',
            ]);

        $pendingApprovers = DB::table('t_WorkFlowPending as p')
            ->join('t_Users as u', 'p.UserId', '=', 'u.Id')
            ->leftJoin('t_WorkFlowStages as ws', 'p.Stage', '=', 'ws.Id')
            ->leftJoin($permissionTable . ' as perm', 'ws.PermissionId', '=', 'perm.id')
            ->whereIn('p.Source', $workflowSources)
            ->where('p.SourceID', (string)$journalEntry->getKey())
            ->whereNull('p.DeletedOn')
            ->orderBy('ws.Order')
            ->get([
                'u.Id as UserId',
                'u.Name as UserName',
                'ws.StageName',
                'ws.Order as StageOrder',
                'perm.name as PermissionName',
            ]);

        $roleNamesByUser = collect();
        $pendingUserIds = $pendingApprovers->pluck('UserId')->unique()->values();
        if ($pendingUserIds->isNotEmpty()) {
            $roleRows = DB::table($modelRolesTable . ' as mr')
                ->join($rolesTable . ' as r', 'mr.' . $rolePivotKey, '=', 'r.id')
                ->whereIn('mr.' . $modelMorphKey, $pendingUserIds)
                ->whereIn('mr.model_type', [User::class, (new User())->getMorphClass()])
                ->select('mr.' . $modelMorphKey . ' as UserId', 'r.name')
                ->get();

            $roleNamesByUser = $roleRows
                ->groupBy('UserId')
                ->map(fn ($rows) => $rows->pluck('name')->unique()->implode(', '));
        }

        $pendingApprovers = $pendingApprovers->map(function ($row) use ($roleNamesByUser) {
            $row->RoleNames = $roleNamesByUser[$row->UserId] ?? '-';

            return $row;
        });

        $isPosted = strtolower($journalEntry->ApprovalStatus ?? '') === 'posted'
            || strtolower($journalEntry->Status ?? '') === 'posted';

        $postedBy = null;
        if ($isPosted) {
            $postedBy = [
                'name' => $journalEntry->modifiedBy->Name ?? $journalEntry->createdBy->Name ?? 'System',
                'time' => $journalEntry->ModifiedOn ?? $journalEntry->CreatedOn,
            ];
        }

        return view('finance.generalledger.journalentry.show', compact(
            'journalEntry',
            'canApprove',
            'hasPendingApprovals',
            'cantApproveReason',
            'workflowHistory',
            'pendingApprovers',
            'postedBy'
        ));
    }

    private function getCantApproveReason(array $sources, string|int $sourceId, $user): ?string
    {
        $pendingRow = DB::table('t_WorkFlowPending')
            ->whereIn('Source', $sources)
            ->where('SourceID', (string)$sourceId)
            ->whereNull('DeletedOn')
            ->orderBy('CreatedOn', 'desc')
            ->first(['Stage', 'UserId']);

        if (! $pendingRow) {
            return 'No pending approvals found for this entry.';
        }

        if ((int)$pendingRow->UserId !== (int)$user->Id) {
            return 'You are not assigned to approve at the current stage.';
        }

        $makerId = DB::table('t_WorkFlowHistory')
            ->whereIn('Source', $sources)
            ->where('SourceID', (string)$sourceId)
            ->whereNull('DeletedOn')
            ->orderBy('CreatedOn', 'asc')
            ->value('CreatedBy');

        if ($makerId && (int)$makerId === (int)$user->Id) {
            return 'You cannot approve your own entry (Maker-Checker policy).';
        }

        if (! empty($pendingRow->Stage)) {
            $stageRow = DB::table('t_WorkFlowStages')
                ->select('PermissionId', 'StageName')
                ->where('Id', (int)$pendingRow->Stage)
                ->first();

            if (! $stageRow) {
                return 'Approval stage not found. Contact an administrator.';
            }

            if ($stageRow->PermissionId) {
                $permissionName = DB::table('t_Permissions')
                    ->where('id', (int)$stageRow->PermissionId)
                    ->value('name');

                if (! $permissionName) {
                    $permissionName = 'workflowstage_' . str_replace(' ', '', (string)$stageRow->StageName);
                    $exists = DB::table('t_Permissions')->where('name', $permissionName)->exists();
                    if (! $exists) {
                        return 'Approval stage permission is not configured.';
                    }
                }

                if (! $user->hasPermissionTo($permissionName)) {
                    return 'You do not have permission for stage: ' . $stageRow->StageName . '.';
                }
            }
        }

        return null;
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerUpdate, FinanceJournalEntry::class);

        $journalEntry = FinanceJournalEntry::with('journalLines')->findOrFail($id);
        $gls = FinanceGLAccounts::select('Id', 'GLName', 'GLCode')->get();
        $branches = Branch::select('Id', 'Name')->get();
        $departments = Department::select('Id', 'Name')->get();

        return view('finance.generalledger.journalentry.edit', compact('journalEntry', 'gls', 'branches', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerUpdate, FinanceJournalEntry::class);

        $journalEntry = FinanceJournalEntry::with('journalLines')->findOrFail($id);

        // Normalize incoming arrays to entries and include optional line_id for diffing
        $entries = [];
        foreach ($request->GLAccount as $index => $gl) {
            $drcr = $request->DRCR[$index] ?? null;
            $amount = (float)($request->Amount[$index] ?? 0);
            $entries[] = [
                'line_id' => $request->LineId[$index] ?? null,
                'gl_id' => $gl,
                'branch_id' => $request->Branch[$index] ?? null,
                'department_id' => $request->Department[$index] ?? null,
                'debit' => $drcr === 'DR' ? $amount : 0,
                'credit' => $drcr === 'CR' ? $amount : 0,
                'is_debit' => $drcr === 'DR',
                'amount' => $amount,
                'narration' => $request->Narration[$index] ?? null,
            ];
        }

        $request->merge(['entries' => $entries]);

        $validated = $request->validate([
            'JournalDate' => 'required|date',
            'Description' => 'nullable|string',
            'entries' => 'required|array|min:2',
            'entries.*.line_id' => 'nullable|integer',
            'entries.*.gl_id' => 'required|exists:t_FinanceGLAccounts,Id',
            'entries.*.branch_id' => 'required|exists:t_Branches,Id',
            'entries.*.department_id' => 'required|exists:t_Departments,Id',
            'entries.*.debit' => 'required|numeric|min:0',
            'entries.*.credit' => 'required|numeric|min:0',
        ]);

        $totalDebit = collect($validated['entries'])->sum('debit');
        $totalCredit = collect($validated['entries'])->sum('credit');
        if ($totalDebit != $totalCredit) {
            return back()->withErrors(['Amount mismatch' => 'Total Debit must equal Total Credit'])->withInput();
        }

        // Guard: ensure provided line_ids (if any) belong to this journal entry
        $existingIds = $journalEntry->journalLines->pluck('Id')->map(fn ($v) => (int)$v)->all();
        $incomingIds = collect($validated['entries'])
            ->pluck('line_id')
            ->filter()
            ->map(fn ($v) => (int)$v)
            ->all();
        foreach ($incomingIds as $lid) {
            if (! in_array($lid, $existingIds, true)) {
                return back()->withErrors(['Invalid line submitted' => 'One or more lines do not belong to this journal entry.'])->withInput();
            }
        }

        DB::beginTransaction();

        try {
            // Update header
            $journalEntry->Date = $request->JournalDate;
            $journalEntry->Description = $request->Description;
            $journalEntry->ModifiedBy = Auth::id();
            $journalEntry->save();

            // Delete removed lines
            $toDelete = array_diff($existingIds, $incomingIds);
            if (! empty($toDelete)) {
                FinanceJournalLines::where('JournalEntryId', $journalEntry->Id)
                    ->whereIn('Id', $toDelete)
                    ->delete();
            }

            // Upsert incoming lines
            foreach ($entries as $entry) {
                $payload = [
                    'GLAccountID' => $entry['gl_id'],
                    'BranchID' => $entry['branch_id'],
                    'DepartmentID' => $entry['department_id'],
                    'IsDebit' => (bool)$entry['is_debit'],
                    'Amount' => (bool)$entry['is_debit'] ? (float)$entry['amount'] * -1 : (float)$entry['amount'],
                    'Debit' => (float)($entry['debit'] * -1 ?? 0),
                    'Credit' => (float)($entry['credit'] ?? 0),
                    'Narration' => $entry['narration'] ?? null,
                    'ModifiedBy' => Auth::id(),
                ];

                if (! empty($entry['line_id'])) {
                    // Update existing
                    FinanceJournalLines::where('JournalEntryId', $journalEntry->Id)
                        ->where('Id', (int)$entry['line_id'])
                        ->update($payload);
                } else {
                    // Create new
                    FinanceJournalLines::create(array_merge($payload, [
                        'JournalEntryId' => $journalEntry->Id,
                        'CreatedBy' => Auth::id(),
                    ]));
                }
            }
            activity('Journal Entry Update')
                ->performedOn(new FinanceJournalEntry())
                ->causedBy(Auth::id())
                ->withProperties(['Update' => $journalEntry])
                ->log('Updated Journal Entry');

            DB::commit();

            return redirect()->route('journalentry.show', $journalEntry->Id)->with('success', 'Journal Entry updated successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return $th->getMessage();
            Log::error('Failed to update Journal Entry ' . $th->getMessage());

            return back()->with('error', 'Failed to update Journal Entry')->withInput();
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerDelete, FinanceJournalEntry::class);

        $entry = FinanceJournalEntry::findOrFail($id);
        DB::transaction(function () use ($entry) {
            FinanceJournalLines::where('JournalEntryId', $entry->Id)->delete();
            $entry->delete();
        });

        return redirect()->route('journalentry.index')->with('success', 'Journal Entry deleted.');
    }

    public function action(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
            'action_type' => 'required|in:approve,reject',
        ]);

        $journalEntry = FinanceJournalEntry::findOrFail($id);
        $journalEntry->ApprovalStatus = $request->action_type === 'approve' ? 'Posted' : 'Rejected';
        $journalEntry->save();

        // Optional: log the reason somewhere if needed
        // JournalApproval::create([...])

        return redirect()->route('journalentry.index')->with('status', 'Journal entry ' . $request->action_type . 'd successfully.');
    }
}
