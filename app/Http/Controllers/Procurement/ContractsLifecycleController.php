<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\ContractMilestone;
use App\Models\Procurement\ContractMilestoneChecklist;
use App\Models\Procurement\ContractPenaltyRule;
use App\Models\Procurement\TenderAward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractsLifecycleController extends Controller
{
    /**
     * Display list of active contracts for lifecycle management
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', TenderAward::class);

        // Calculate counts
        $counts = TenderAward::selectRaw("
            count(case when ContractStatus = 'Approved' then 1 end) as pending,
            count(case when ContractStatus = 'Executed' then 1 end) as active,
            count(case when ContractStatus = 'Terminated' then 1 end) as terminated,
            count(*) as total
        ")->first();

        // Base query
        $query = TenderAward::with(['tender', 'winningSupplier'])
            ->whereIn('ContractStatus', ['Approved', 'Executed', 'Terminated']);

        // Apply Status Filter
        if ($request->filled('status_filter')) {
            $query->where('ContractStatus', $request->status_filter);
        }

        // Apply Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ContractRef', 'like', "%{$search}%")
                  ->orWhere('Title', 'like', "%{$search}%") // Assuming Title exists or similar
                  ->orWhereHas('winningSupplier.supplierMaster.party', function ($q) use ($search) {
                      $q->where('ThirdPartyName', 'like', "%{$search}%")
                        ->orWhere('TradingName', 'like', "%{$search}%");
                  })
                  ->orWhereHas('tender', function ($q) use ($search) {
                      $q->where('TenderNo', 'like', "%{$search}%")
                        ->orWhere('Title', 'like', "%{$search}%");
                  });
            });
        }

        $contracts = $query->orderBy('ContractApprovedOn', 'desc')->paginate(15);

        return view('procurement.contracts.contractlifecycle.index', compact('contracts', 'counts'));
    }

    /**
     * View detailed contract information
     */
    public function view($Id)
    {
        $contract = TenderAward::with(['tender', 'winningSupplier'])
            ->findOrFail($Id);
        $this->authorize('view', $contract);

        return view('procurement.contracts.contractlifecycle.view', compact('contract'));
    }

    /**
     * Monitor contract execution
     */
    public function monitorExecution($id)
    {
        $contract = TenderAward::with(['tender', 'winningSupplier'])
            ->findOrFail($id);
        $this->authorize('view', $contract);

        $milestones = ContractMilestone::with(['checklistItems'])
            ->where('ContractSourceType', 'tender')
            ->where('ContractSourceID', $contract->Id)
            ->orderBy('MilestoneNo')
            ->orderBy('Id')
            ->get();

        $penaltyRule = ContractPenaltyRule::where('ContractSourceType', 'tender')
            ->where('ContractSourceID', $contract->Id)
            ->whereNull('MilestoneID')
            ->where('IsActive', true)
            ->latest('Id')
            ->first();

        return view('procurement.contracts.contractlifecycle.execution', compact('contract', 'milestones', 'penaltyRule'));
    }

    /**
     * Show contract amendment form
     */
    public function amend($id)
    {
        $contract = TenderAward::with(['tender', 'winningSupplier'])
            ->findOrFail($id);
        $this->authorize('update', $contract);

        return view('procurement.contracts.contractlifecycle.amend', compact('contract'));
    }

    /**
     * Submit contract amendment
     */
    public function submitAmendment(Request $request, $id)
    {
        $request->validate([
            'amendment_reason' => 'required|string|max:500',
            'amendment_details' => 'required|string',
            'new_contract_value' => 'nullable|numeric|min:0',
            'new_end_date' => 'nullable|date',
        ]);

        $contract = TenderAward::findOrFail($id);
        $this->authorize('update', $contract);

        // TODO: Create amendment record and update contract
        // For now, just redirect with success message

        return redirect()->route('contracts.lifecycle.view', $id)
            ->with('success', 'Contract amendment submitted successfully.');
    }

    /**
     * Show contract termination form
     */
    public function terminate($id)
    {
        $contract = TenderAward::with(['tender', 'winningSupplier'])
            ->findOrFail($id);
        $this->authorize('update', $contract);

        return view('procurement.contracts.contractlifecycle.terminate', compact('contract'));
    }

    /**
     * Submit contract termination
     */
    public function submitTermination(Request $request, $id)
    {
        $request->validate([
            'termination_reason' => 'required|string|max:500',
            'termination_date' => 'required|date',
            'settlement_details' => 'nullable|string',
        ]);

        $contract = TenderAward::findOrFail($id);

        $contract->update([
            'ContractStatus' => 'Terminated',
            'TerminationReason' => $request->termination_reason,
            'TerminationDate' => $request->termination_date,
            'SettlementDetails' => $request->settlement_details,
            'ModifiedBy' => Auth::id(),
        ]);

        return redirect()->route('contracts.lifecycle.index')
            ->with('success', 'Contract terminated successfully.');
    }

    /**
     * Execute/Activate the contract
     */
    public function executeAction(Request $request, $id)
    {
        $contract = TenderAward::findOrFail($id);
        $this->authorize('update', $contract);

        if ($contract->ContractStatus !== 'Approved') {
            return redirect()->back()->with('error', 'Only approved contracts can be executed.');
        }

        $contract->update([
            'ContractStatus' => 'Executed',
            'ModifiedBy' => Auth::id(),
            // Ensure dates are set if they were missing or verify them?
            // Assuming dates were set during contract creation/management.
        ]);

        return redirect()->route('contracts.lifecycle.index')
            ->with('success', 'Contract executed and is now Active.');
    }

    public function milestoneStore(Request $request, $id)
    {
        $contract = TenderAward::findOrFail($id);
        $this->authorize('update', $contract);

        $validated = $request->validate([
            'MilestoneNo' => 'required|integer|min:1',
            'Title' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'PlannedDueDate' => 'nullable|date',
            'ValueType' => 'required|in:PERCENT,FIXED',
            'ValuePercent' => 'nullable|required_if:ValueType,PERCENT|numeric|min:0|max:100',
            'ValueAmount' => 'nullable|required_if:ValueType,FIXED|numeric|min:0',
            'AcceptanceRequired' => 'nullable|boolean',
        ]);

        ContractMilestone::create([
            'ContractSourceType' => 'tender',
            'ContractSourceID' => $contract->Id,
            'MilestoneNo' => $validated['MilestoneNo'],
            'Title' => $validated['Title'],
            'Description' => $validated['Description'] ?? null,
            'PlannedDueDate' => $validated['PlannedDueDate'] ?? null,
            'ValueType' => $validated['ValueType'],
            'ValuePercent' => $validated['ValueType'] === 'PERCENT' ? ($validated['ValuePercent'] ?? null) : null,
            'ValueAmount' => $validated['ValueType'] === 'FIXED' ? ($validated['ValueAmount'] ?? null) : null,
            'AcceptanceRequired' => (bool) ($validated['AcceptanceRequired'] ?? true),
            'Status' => 'Draft',
        ]);

        return redirect()
            ->route('contracts.lifecycle.execution', $contract->Id)
            ->with('success', 'Milestone added.');
    }

    public function checklistStore(Request $request, $id, $milestoneId)
    {
        $contract = TenderAward::findOrFail($id);
        $this->authorize('update', $contract);

        $milestone = ContractMilestone::where('Id', $milestoneId)
            ->where('ContractSourceType', 'tender')
            ->where('ContractSourceID', $contract->Id)
            ->firstOrFail();

        $validated = $request->validate([
            'ItemDescription' => 'required|string|max:255',
            'Required' => 'nullable|boolean',
            'Notes' => 'nullable|string',
        ]);

        ContractMilestoneChecklist::create([
            'MilestoneID' => $milestone->Id,
            'ItemDescription' => $validated['ItemDescription'],
            'Required' => (bool) ($validated['Required'] ?? true),
            'IsFulfilled' => false,
            'Notes' => $validated['Notes'] ?? null,
        ]);

        return redirect()
            ->route('contracts.lifecycle.execution', $contract->Id)
            ->with('success', 'Checklist item added.');
    }

    public function checklistToggle(Request $request, $id, $milestoneId, $checklistId)
    {
        $contract = TenderAward::findOrFail($id);
        $this->authorize('update', $contract);

        $milestone = ContractMilestone::where('Id', $milestoneId)
            ->where('ContractSourceType', 'tender')
            ->where('ContractSourceID', $contract->Id)
            ->firstOrFail();

        $checklist = ContractMilestoneChecklist::where('Id', $checklistId)
            ->where('MilestoneID', $milestone->Id)
            ->firstOrFail();

        $isFulfilled = $request->boolean('IsFulfilled');

        $checklist->update([
            'IsFulfilled' => $isFulfilled,
            'FulfilledBy' => $isFulfilled ? Auth::id() : null,
            'FulfilledOn' => $isFulfilled ? now() : null,
            'Notes' => $request->input('Notes', $checklist->Notes),
        ]);

        return redirect()
            ->route('contracts.lifecycle.execution', $contract->Id)
            ->with('success', 'Checklist updated.');
    }

    public function milestoneStatus(Request $request, $id, $milestoneId)
    {
        $contract = TenderAward::findOrFail($id);
        $this->authorize('update', $contract);

        $milestone = ContractMilestone::where('Id', $milestoneId)
            ->where('ContractSourceType', 'tender')
            ->where('ContractSourceID', $contract->Id)
            ->with('checklistItems')
            ->firstOrFail();

        $action = $request->input('action');
        if (!in_array($action, ['submit', 'accept', 'reject', 'waive'], true)) {
            return redirect()
                ->route('contracts.lifecycle.execution', $contract->Id)
                ->with('error', 'Invalid milestone action.');
        }

        if ($action === 'submit') {
            $milestone->Status = 'Submitted';
        }

        if ($action === 'reject') {
            $milestone->Status = 'Rejected';
        }

        if ($action === 'waive') {
            $request->validate([
                'waive_reason' => 'required|string|max:500',
            ]);
            $milestone->Status = 'Waived';
            $milestone->WaivedBy = Auth::id();
            $milestone->WaivedOn = now();
            $milestone->WaiveReason = $request->input('waive_reason');
        }

        if ($action === 'accept') {
            $incompleteRequired = $milestone->checklistItems
                ->where('Required', true)
                ->where('IsFulfilled', false)
                ->count();

            if ($incompleteRequired > 0) {
                return redirect()
                    ->route('contracts.lifecycle.execution', $contract->Id)
                    ->with('error', 'Cannot accept milestone while required checklist items are incomplete.');
            }

            $milestone->Status = 'Accepted';
            $milestone->AcceptedBy = Auth::id();
            $milestone->AcceptedOn = now();
        }

        $milestone->save();

        return redirect()
            ->route('contracts.lifecycle.execution', $contract->Id)
            ->with('success', 'Milestone status updated.');
    }
}
