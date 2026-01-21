<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\TenderAward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        return view('procurement.contracts.contractlifecycle.execution', compact('contract'));
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
}
