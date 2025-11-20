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
    public function index()
    {
        // Load signed/executed contracts
        $contracts = TenderAward::with(['tender', 'winningSupplier'])
            ->whereIn('ContractStatus', ['Approved', 'Executed'])
            ->orderBy('ContractApprovedOn', 'desc')
            ->paginate(15);

        return view('procurement.contracts.contractlifecycle.index', compact('contracts'));
    }

    /**
     * View detailed contract information
     */
    public function view($id)
    {
        $contract = TenderAward::with(['tender', 'winningSupplier'])
            ->findOrFail($id);

        return view('procurement.contracts.contractlifecycle.view', compact('contract'));
    }

    /**
     * Monitor contract execution
     */
    public function monitorExecution($id)
    {
        $contract = TenderAward::with(['tender', 'winningSupplier'])
            ->findOrFail($id);

        return view('procurement.contracts.contractlifecycle.execution', compact('contract'));
    }

    /**
     * Show contract amendment form
     */
    public function amend($id)
    {
        $contract = TenderAward::with(['tender', 'winningSupplier'])
            ->findOrFail($id);

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
}
