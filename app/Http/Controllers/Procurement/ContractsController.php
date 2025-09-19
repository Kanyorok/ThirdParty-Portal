<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\TenderAward;
use App\Models\Procurement\Tender;
use App\Models\ThirdParies\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContractsController extends Controller
{
    /**
     * Display contracts list
     */
    public function index(Request $request)
    {
        $query = TenderAward::with(['tender', 'winningSupplier'])
            ->where('AwardStatus', 'Approved');

        // Apply filters
        if ($request->filled('status_filter')) {
            $query->where('ContractStatus', $request->status_filter);
        }

        if ($request->filled('search')) {
            $query->whereHas('tender', function ($q) use ($request) {
                $q->where('TenderNo', 'like', '%' . $request->search . '%')
                  ->orWhere('Title', 'like', '%' . $request->search . '%');
            });
        }

        $contracts = $query->orderBy('ApprovedOn', 'desc')->paginate(15);
        
        return view('procurement.contracts.contractcreation.index', compact('contracts'))
            ->with('filters', $request->only(['status_filter', 'search']));
    }

    /**
     * Show contract creation form from award
     */
    public function create(Request $request)
    {
        $awardId = $request->get('award_id');
        $award = null;

        if ($awardId) {
            $award = TenderAward::with(['tender', 'winningSupplier'])
                ->where('Id', $awardId)
                ->where('AwardStatus', 'Approved')
                ->first();
                
            if (!$award) {
                return redirect()->route('contracts.index')
                    ->with('error', 'Award not found or not approved yet.');
            }
        }

        return view('procurement.contracts.contractcreation.create', compact('award'));
    }

    /**
     * Store new contract
     */
    public function store(Request $request)
    {
        $request->validate([
            'award_id' => 'required|exists:t_TenderAwards,Id',
            'contract_type' => 'required|in:procurement_managed,legal_managed',
            'contract_title' => 'required|string|max:255',
            'contract_description' => 'required|string',
            'contract_value' => 'required|numeric|min:0',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'payment_terms' => 'required|string',
            'delivery_terms' => 'nullable|string',
            'special_conditions' => 'nullable|string',
        ]);

        DB::beginTransaction();
        
        try {
            $award = TenderAward::findOrFail($request->award_id);
            
            if ($request->contract_type === 'legal_managed') {
                // Send request to legal module
                $legalRequest = $this->sendToLegalModule($award, $request->all());
                
                // Update award with contract request reference
                $award->update([
                    'ContractStatus' => 'Sent to Legal',
                    'ContractRequestRef' => $legalRequest['reference'] ?? null,
                    'ModifiedBy' => Auth::id(),
                ]);
                
                DB::commit();
                
                return redirect()->route('contracts.index')
                    ->with('success', 'Contract request sent to Legal Department successfully. Reference: ' . ($legalRequest['reference'] ?? 'N/A'));
                    
            } else {
                // Create contract within procurement
                $contractRef = $this->generateContractReference($award);
                
                // Update award with contract details
                $award->update([
                    'ContractStatus' => 'Draft Created',
                    'ContractRef' => $contractRef,
                    'ContractValue' => $request->contract_value,
                    'ContractStartDate' => $request->start_date,
                    'ContractEndDate' => $request->end_date,
                    'PaymentTerms' => $request->payment_terms,
                    'DeliveryTerms' => $request->delivery_terms,
                    'SpecialConditions' => $request->special_conditions,
                    'ModifiedBy' => Auth::id(),
                ]);
                
                DB::commit();
                
                return redirect()->route('contracts.show', $award->Id)
                    ->with('success', 'Contract created successfully. Reference: ' . $contractRef);
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create contract: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * View contract details
     */
    public function show($id)
    {
        $contract = TenderAward::with(['tender', 'winningSupplier'])
            ->findOrFail($id);
            
        return view('procurement.contracts.contractcreation.show', compact('contract'));
    }

    /**
     * Edit contract
     */
    public function edit($id)
    {
        $award = TenderAward::with(['tender', 'winningSupplier'])
            ->findOrFail($id);
            
        return view('procurement.contracts.contractcreation.edit', compact('award'));
    }

    /**
     * Update contract
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'contract_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'payment_terms' => 'required|string',
            'delivery_terms' => 'nullable|string',
            'special_conditions' => 'nullable|string',
        ]);

        $award = TenderAward::findOrFail($id);
        
        $award->update([
            'ContractValue' => $request->contract_value,
            'ContractStartDate' => $request->start_date,
            'ContractEndDate' => $request->end_date,
            'PaymentTerms' => $request->payment_terms,
            'DeliveryTerms' => $request->delivery_terms,
            'SpecialConditions' => $request->special_conditions,
            'ModifiedBy' => Auth::id(),
        ]);

        return redirect()->route('contracts.show', $id)
            ->with('success', 'Contract updated successfully.');
    }

    /**
     * Contract approval queue
     */
    public function approvalQueue()
    {
        $contracts = TenderAward::with(['tender', 'winningSupplier'])
            ->whereIn('ContractStatus', ['Draft Created', 'Under Review'])
            ->orderBy('CreatedOn', 'desc')
            ->paginate(15);
            
        return view('procurement.contracts.contractcreation.approve_index', compact('contracts'));
    }

    /**
     * Approve contract
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'approval_remarks' => 'nullable|string|max:500',
        ]);

        $award = TenderAward::findOrFail($id);
        
        $award->update([
            'ContractStatus' => 'Approved',
            'ContractApprovalRemarks' => $request->approval_remarks,
            'ContractApprovedBy' => Auth::id(),
            'ContractApprovedOn' => now(),
            'ModifiedBy' => Auth::id(),
        ]);

        return redirect()->route('contracts.approvalQueue')
            ->with('success', 'Contract approved successfully.');
    }

    /**
     * Send contract request to legal module
     */
    protected function sendToLegalModule($award, $contractData)
    {
        // TODO: Integrate with Legal module API or service
        // For now, return mock response
        return [
            'success' => true,
            'reference' => 'LEGAL-REQ-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'message' => 'Contract request submitted to Legal Department'
        ];
    }

    /**
     * Generate contract reference
     */
    protected function generateContractReference($award)
    {
        $year = date('Y');
        $sequence = str_pad(($award->Id ?? rand(1, 999)), 3, '0', STR_PAD_LEFT);
        return "CONTRACT/PROC/{$year}/{$sequence}";
    }

    /**
     * Legal Integration page
     */
    public function legalIntegration()
    {
        $legalRequests = TenderAward::with(['tender', 'winningSupplier'])
            ->where('ContractStatus', 'Sent to Legal')
            ->orderBy('ModifiedOn', 'desc')
            ->paginate(15);
            
        return view('procurement.contracts.legal.index', compact('legalRequests'));
    }

    /**
     * Create contract from approved award (direct link from awards page)
     */
    public function createFromAward($awardId)
    {
        $award = TenderAward::with(['tender', 'winningSupplier'])
            ->where('Id', $awardId)
            ->where('AwardStatus', 'Approved')
            ->first();
            
        if (!$award) {
            return redirect()->route('procawards.index')
                ->with('error', 'Award not found or not approved yet.');
        }

        return redirect()->route('contracts.create', ['award_id' => $awardId])
            ->with('success', 'Ready to create contract for approved tender award.');
    }
}
