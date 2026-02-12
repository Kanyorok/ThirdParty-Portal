<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\ContractPenaltyRule;
use App\Models\Procurement\TenderAward;
use App\Models\Procurement\Tender;
use App\Models\Procurement\RFQAward;
use App\Models\Procurement\RFQ;
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
        // 1. Fetch Tender Awards with Contracts
        $tenderQuery = TenderAward::with(['tender', 'winningSupplier.thirdParty'])
            ->whereNotNull('ContractStatus'); // Only those with contract activity

        // 2. Fetch RFQ Awards with Contracts
        $rfqQuery = RFQAward::with(['supplier.supplierMaster.party'])
            ->whereNotNull('ContractStatus'); // Only those with contract activity

        // Apply filters to both queries
        if ($request->filled('status_filter')) {
            $tenderQuery->where('ContractStatus', $request->status_filter);
            $rfqQuery->where('ContractStatus', $request->status_filter);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $tenderQuery->whereHas('tender', function ($q) use ($search) {
                $q->where('TenderNo', 'like', '%' . $search . '%')
                    ->orWhere('Title', 'like', '%' . $search . '%');
            });
            
            // For RFQ, we need to search related RFQ model
            // This is a bit complex with Eloquent, so we might filter after fetch or use whereHas if relationship exists
            // Assuming RFQAward has 'rfq' relationship (which we used in other methods via RFQ::find)
            // But RFQAward model doesn't seem to have 'rfq' relation defined standardly, we used manual lookup.
            // For simplicity in search, we'll filter the collection after fetching for RFQs if needed, 
            // or just fetch all and filter in memory if dataset isn't huge.
            // Given the context, let's fetch and map, then filter.
        }

        $tenderContracts = $tenderQuery->orderBy('ApprovedOn', 'desc')->get();
        $rfqContracts = $rfqQuery->orderBy('CreatedOn', 'desc')->get();

        // Map RFQ contracts to unified structure
        foreach ($rfqContracts as $contract) {
            $contract->type = 'rfq';
            $rfq = RFQ::find($contract->RFQId);
            $contract->tender = $rfq;
            if ($rfq) {
                $contract->tender->TenderNo = $rfq->RFQNumber;
                $contract->tender->Title = $rfq->Subject;
                // Map Currency if exists in RFQ, otherwise default
                $contract->tender->Currency = (object)['Code' => $rfq->Currency ?? 'KES'];
            }
            $contract->winningSupplier = $contract->supplier;
            // Ensure AwardDate is set (RFQAward might use CreatedOn or similar)
            if (!$contract->AwardDate) {
                $contract->AwardDate = $contract->CreatedOn;
            }
        }

        foreach ($tenderContracts as $contract) {
            $contract->type = 'tender';
        }

        // Merge and Sort
        $allContracts = $tenderContracts->merge($rfqContracts)->sortByDesc(function ($contract) {
            return $contract->ContractApprovedOn ?? $contract->CreatedOn;
        });

        // Apply Search Filter on the merged collection if search is present (for RFQ specifically, or both)
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $allContracts = $allContracts->filter(function ($contract) use ($search) {
                $ref = strtolower($contract->tender?->TenderNo ?? '');
                $title = strtolower($contract->tender?->Title ?? '');
                $supplier = strtolower(
                    $contract->winningSupplier?->thirdParty?->TradingName 
                    ?? $contract->winningSupplier?->supplierMaster?->party?->TradingName 
                    ?? ''
                );
                
                return str_contains($ref, $search) || str_contains($title, $search) || str_contains($supplier, $search);
            });
        }

        // Manual Pagination
        $page = $request->input('page', 1);
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        
        $contracts = new \Illuminate\Pagination\LengthAwarePaginator(
            $allContracts->slice($offset, $perPage)->values(),
            $allContracts->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('procurement.contracts.contractcreation.index', compact('contracts'))
            ->with('filters', $request->only(['status_filter', 'search']));
    }

    /**
     * Show contract creation form from award
     */
    public function create(Request $request)
    {
        $this->authorize('create', TenderAward::class);
        $awardId = $request->get('award_id');
        $awardType = $request->get('award_type', 'tender');
        $award = null;

        if ($awardId) {
            if ($awardType === 'rfq') {
                $award = RFQAward::with(['supplier.supplierMaster.party'])
                    ->where('Id', $awardId)
                    ->first();
                
                if ($award) {
                    // Map RFQ award to look like Tender award for the view
                    $rfq = RFQ::find($award->RFQId);
                    $award->tender = $rfq;
                    if ($rfq) {
                        $award->tender->TenderNo = $rfq->RFQNumber;
                        $award->tender->Title = $rfq->Subject;
                    }
                    $award->winningSupplier = $award->supplier;
                }
            } else {
                $award = TenderAward::with(['tender', 'winningSupplier'])
                    ->where('Id', $awardId)
                    ->where('AwardStatus', 'Approved')
                    ->first();
            }

            if (!$award) {
                return redirect()->route('contracts.index')
                    ->with('error', 'Award not found or not approved yet.');
            }
        }

        return view('procurement.contracts.contractcreation.create', compact('award', 'awardType'));
    }

    /**
     * Store new contract
     */
    /**
     * Store new contract
     */
    public function store(Request $request)
    {
        $type = $request->input('award_type', 'tender');
        $table = $type === 'rfq' ? 't_RFQAward' : 't_TenderAwards';

        $request->validate([
            'award_id' => "required|exists:$table,Id",
            'award_type' => 'nullable|in:tender,rfq',
            'contract_type' => 'required|in:procurement_managed,legal_managed',
            'contract_title' => 'required|string|max:255',
            'contract_description' => 'required|string',
            'contract_value' => 'required|numeric|min:0',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'payment_terms' => 'required|string',
            'delivery_terms' => 'nullable|string',
            'special_conditions' => 'nullable|string',
            'penalty_type' => 'nullable|in:PER_DAY_DELAY,PERCENT,FIXED',
            'penalty_rate' => 'nullable|numeric|min:0',
            'grace_days' => 'nullable|integer|min:0',
            'cap_amount' => 'nullable|numeric|min:0',
            'cap_percent' => 'nullable|numeric|min:0',
            'apply_method' => 'nullable|in:DEDUCT_FROM_PAYMENT,DEBIT_NOTE',
            'requires_approval_to_apply' => 'nullable|boolean',
            'requires_approval_to_waive' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $award = null;
            if ($type === 'rfq') {
                $award = RFQAward::findOrFail($request->award_id);
            } else {
                $award = TenderAward::findOrFail($request->award_id);
            }

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

                $this->upsertPenaltyRule($type, (int) $award->Id, $request);

                DB::commit();

                return redirect()->route('contracts.show', ['id' => $award->Id, 'type' => $type])
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
    public function show(Request $request, $id)
    {
        $type = $request->query('type', 'tender');
        $contract = null;

        if ($type === 'rfq') {
            $contract = RFQAward::with(['supplier.supplierMaster.party'])
                ->findOrFail($id);
            
            // Map RFQ to Tender structure for view
            $rfq = RFQ::find($contract->RFQId);
            $contract->tender = $rfq;
            if ($rfq) {
                $contract->tender->TenderNo = $rfq->RFQNumber;
                $contract->tender->Title = $rfq->Subject;
            }
            $contract->winningSupplier = $contract->supplier;
        } else {
            $contract = TenderAward::with(['tender', 'winningSupplier.thirdParty'])
                ->findOrFail($id);
        }

        try {
            $workflow = $this->getWorkflow($type);
            $history = $workflow->historyForModel($contract);
            $canApprove = $workflow->canApproveModel($contract, Auth::user());
        } catch (\Exception $e) {
            \Log::error('Workflow data fetch error: ' . $e->getMessage());
            $history = collect();
            $canApprove = false;
        }

        $penaltyRule = $this->getActivePenaltyRule($type, (int) $contract->Id);

        return view('procurement.contracts.contractcreation.show', compact('contract', 'type', 'history', 'canApprove', 'penaltyRule'));
    }

    /**
     * Edit contract
     */
    public function edit(Request $request, $id)
    {
        $type = $request->query('type', 'tender');
        $award = null;

        if ($type === 'rfq') {
            $award = RFQAward::with(['supplier.supplierMaster.party'])
                ->findOrFail($id);
            
            // Map RFQ to Tender structure
            $rfq = RFQ::find($award->RFQId);
            $award->tender = $rfq;
            if ($rfq) {
                $award->tender->TenderNo = $rfq->RFQNumber;
                $award->tender->Title = $rfq->Subject;
            }
            $award->winningSupplier = $award->supplier;
        } else {
            $award = TenderAward::with(['tender', 'winningSupplier'])
                ->findOrFail($id);
        }

        $penaltyRule = $this->getActivePenaltyRule($type, (int) $award->Id);

        return view('procurement.contracts.contractcreation.edit', compact('award', 'type', 'penaltyRule'));
    }

    /**
     * Update contract
     */
    public function update(Request $request, $id)
    {
        $type = $request->input('award_type', 'tender');
        
        $request->validate([
            'contract_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'payment_terms' => 'required|string',
            'delivery_terms' => 'nullable|string',
            'special_conditions' => 'nullable|string',
            'penalty_type' => 'nullable|in:PER_DAY_DELAY,PERCENT,FIXED',
            'penalty_rate' => 'nullable|numeric|min:0',
            'grace_days' => 'nullable|integer|min:0',
            'cap_amount' => 'nullable|numeric|min:0',
            'cap_percent' => 'nullable|numeric|min:0',
            'apply_method' => 'nullable|in:DEDUCT_FROM_PAYMENT,DEBIT_NOTE',
            'requires_approval_to_apply' => 'nullable|boolean',
            'requires_approval_to_waive' => 'nullable|boolean',
        ]);

        $award = null;
        if ($type === 'rfq') {
            $award = RFQAward::findOrFail($id);
        } else {
            $award = TenderAward::findOrFail($id);
        }

        $award->update([
            'ContractValue' => $request->contract_value,
            'ContractStartDate' => $request->start_date,
            'ContractEndDate' => $request->end_date,
            'PaymentTerms' => $request->payment_terms,
            'DeliveryTerms' => $request->delivery_terms,
            'SpecialConditions' => $request->special_conditions,
            'ModifiedBy' => Auth::id(),
        ]);

        $this->upsertPenaltyRule($type, (int) $award->Id, $request);

        return redirect()->route('contracts.show', ['id' => $id, 'type' => $type])
            ->with('success', 'Contract updated successfully.');
    }

    /**
     * Contract approval queue
     */
    /**
     * Contract approval queue
     */
    public function approvalQueue()
    {
        $tenderContracts = TenderAward::with(['tender', 'winningSupplier.thirdParty'])
            ->whereIn('ContractStatus', ['Draft Created', 'rv', 'Under Review']) // Include 'rv' and legacy
            ->orderBy('CreatedOn', 'desc')
            ->get();

        $rfqContracts = RFQAward::with(['supplier.supplierMaster.party'])
            ->whereIn('ContractStatus', ['Draft Created', 'rv', 'Under Review']) // Include 'rv' and legacy
            ->orderBy('CreatedOn', 'desc')
            ->get();
        
        // Map RFQ contracts to look like Tender contracts
        foreach ($rfqContracts as $contract) {
            $contract->type = 'rfq';
            $rfq = RFQ::find($contract->RFQId);
            $contract->tender = $rfq;
            if ($rfq) {
                $contract->tender->TenderNo = $rfq->RFQNumber;
                $contract->tender->Title = $rfq->Subject;
            }
            $contract->winningSupplier = $contract->supplier;
        }

        foreach ($tenderContracts as $contract) {
            $contract->type = 'tender';
        }

        // Merge and Sort
        $allContracts = $tenderContracts->merge($rfqContracts)->sortByDesc('CreatedOn');

        // Manual Pagination
        $page = request()->input('page', 1);
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        
        $contracts = new \Illuminate\Pagination\LengthAwarePaginator(
            $allContracts->slice($offset, $perPage)->values(),
            $allContracts->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('procurement.contracts.contractcreation.approve_index', compact('contracts'));
    }

    protected $workflow;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get workflow service instance based on award type
     */
    private function getWorkflow($type)
    {
        // specific code IDs based on config/workflow.php
        $codeId = ($type === 'rfq') ? 'rfq_award' : 'tender_award';
        return new \App\Services\Workflow\ApprovalWorkflow($codeId, 'ContractStatus');
    }

    /**
     * Approve contract
     */
    public function approve(Request $request, $id)
    {
        $type = $request->input('award_type', 'tender');
        
        $request->validate([
            'approval_remarks' => 'nullable|string|max:500',
        ]);

        $award = null;
        if ($type === 'rfq') {
            $award = RFQAward::findOrFail($id);
        } else {
            $award = TenderAward::findOrFail($id);
        }

        try {
            $workflow = $this->getWorkflow($type);
            
            // Use Approved status which maps to 'Approved' text in config for these types
            $workflow->approve(
                $award,
                Auth::user(),
                \App\Enums\WorkflowStatus::APPROVED, // Maps to 'Ap' which is seeded
                $request->approval_remarks ?? 'Approved',
                'ContractStatus'
            );

            // Manual update just in case workflow doesn't handle non-standard status columns seamlessly depending on version
            // But workflow service usually handles it if column passed. 
            // We keep specific field updates like user/time if workflow doesn't do it automatically for these specific custom fields.
            $award->update([
               // 'ContractStatus' => 'Approved', // Workflow should handle this
                'ContractApprovalRemarks' => $request->approval_remarks,
                'ContractApprovedBy' => Auth::id(),
                'ContractApprovedOn' => now(),
                'ModifiedBy' => Auth::id(),
            ]);

            return redirect()->route('contracts.approvalQueue')
                ->with('success', 'Contract approved successfully.');
        } catch (\Exception $e) {
            \Log::error('Contract approval error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Reject contract
     */
    public function rejectContract(Request $request, $id)
    {
        $type = $request->input('award_type', 'tender');

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $award = null;
        if ($type === 'rfq') {
            $award = RFQAward::findOrFail($id);
        } else {
            $award = TenderAward::findOrFail($id);
        }

        try {
            $workflow = $this->getWorkflow($type);
            
            // Use Rejected status
            $workflow->reject(
                $award,
                Auth::user(),
                \App\Enums\WorkflowStatus::REJECTED, // Maps to 'Re'
                $request->rejection_reason,
                'ContractStatus'
            );

            // Log the rejection details in custom fields if needed
            // Workflow handles status and history.
             \Log::info('Contract rejected', [
                'award_id' => $id,
                'award_type' => $type,
                'rejection_reason' => $request->rejection_reason,
                'rejected_by' => Auth::id()
            ]);

            return redirect()->route('contracts.approvalQueue')
                ->with('warning', 'Contract rejected and returned to draft status for revision.');
        } catch (\Exception $e) {
            \Log::error('Contract rejection error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Rejection failed: ' . $e->getMessage());
        }
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

    protected function getActivePenaltyRule(string $type, int $contractId): ?ContractPenaltyRule
    {
        return ContractPenaltyRule::where('ContractSourceType', $type)
            ->where('ContractSourceID', $contractId)
            ->whereNull('MilestoneID')
            ->where('IsActive', true)
            ->latest('Id')
            ->first();
    }

    protected function upsertPenaltyRule(string $type, int $contractId, Request $request): void
    {
        if (!$request->filled('penalty_type')) {
            ContractPenaltyRule::where('ContractSourceType', $type)
                ->where('ContractSourceID', $contractId)
                ->whereNull('MilestoneID')
                ->update(['IsActive' => false]);
            return;
        }

        ContractPenaltyRule::updateOrCreate(
            [
                'ContractSourceType' => $type,
                'ContractSourceID' => $contractId,
                'MilestoneID' => null,
            ],
            [
                'PenaltyType' => $request->input('penalty_type'),
                'Rate' => $request->input('penalty_rate'),
                'GraceDays' => (int) $request->input('grace_days', 0),
                'CapAmount' => $request->input('cap_amount'),
                'CapPercent' => $request->input('cap_percent'),
                'ApplyMethod' => $request->input('apply_method', 'DEDUCT_FROM_PAYMENT'),
                'RequiresApprovalToApply' => (bool) $request->boolean('requires_approval_to_apply'),
                'RequiresApprovalToWaive' => (bool) $request->boolean('requires_approval_to_waive'),
                'IsActive' => true,
            ]
        );
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
    public function createFromAward(Request $request, $awardId)
    {
        $type = $request->query('type', 'tender');
        $award = null;

        if ($type === 'rfq') {
            $award = RFQAward::where('Id', $awardId)->first();
        } else {
            $award = TenderAward::where('Id', $awardId)
                ->where('AwardStatus', 'Approved')
                ->first();
        }

        if (!$award) {
            return redirect()->route('procawards.index')
                ->with('error', 'Award not found or not approved yet.');
        }

        return redirect()->route('contracts.create', ['award_id' => $awardId, 'award_type' => $type])
            ->with('success', 'Ready to create contract for approved ' . strtoupper($type) . ' award.');
    }

    /**
     * Submit contract for review
     */
    public function submitForReview(Request $request, $id)
    {
        $type = $request->input('award_type', 'tender');
        
        $request->validate([
            'review_notes' => 'nullable|string|max:500',
        ]);

        $award = null;
        if ($type === 'rfq') {
            $award = RFQAward::findOrFail($id);
        } else {
            $award = TenderAward::findOrFail($id);
        }

        // Maker-Checker: Prevent re-submission if already under review or approved
        $allowedStatuses = ['Draft Created', 'Dr', 'Rejected', 'Re'];
        if (!in_array($award->ContractStatus, $allowedStatuses)) {
             return redirect()->back()->with('warning', 'This contract has already been submitted for approval.');
        }

        try {
            $workflow = $this->getWorkflow($type);

            // Use UnderReview status (rv)
            $workflow->submit(
                $award,
                Auth::user(),
                \App\Enums\WorkflowStatus::UnderReview, // Maps to 'rv' which is seeded
                $request->review_notes ?? 'Submitted for Contract Review'
            );

            // Manual update just in case workflow doesn't handle non-standard status columns seamlessly depending on version
            // But workflow service usually handles it if column passed. 
            // We keep specific field updates like user/time if workflow doesn't do it automatically for these specific custom fields.
            $award->update([
                'ContractStatus' => 'Under Review', // Ensure status is updated
                'ContractApprovalRemarks' => $request->review_notes,
                'ModifiedBy' => Auth::id(),
            ]);

            return redirect()->route('contracts.show', ['id' => $id, 'type' => $type])
                ->with('success', 'Contract submitted for review successfully.');

        } catch (\Exception $e) {
            \Log::error('Contract submission error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Submission failed: ' . $e->getMessage()); 
        }
    }

    /**
     * Execute contract
     */
    public function execute(Request $request, $id)
    {
        $type = $request->input('award_type', 'tender');
        
        $award = null;
        if ($type === 'rfq') {
            $award = RFQAward::findOrFail($id);
        } else {
            $award = TenderAward::findOrFail($id);
        }

        // Validate current status
        if ($award->ContractStatus !== 'Approved') {
            return redirect()->back()->with('error', 'Only approved contracts can be executed.');
        }

        try {
            // Update status to Executed
            $award->update([
                'ContractStatus' => 'Executed',
                'ModifiedBy' => Auth::id(),
            ]);

            return redirect()->route('contracts.show', ['id' => $id, 'type' => $type])
                ->with('success', 'Contract executed successfully. It is now active.');

        } catch (\Exception $e) {
            \Log::error('Contract execution error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Execution failed: ' . $e->getMessage());
        }
    }

    /**
     * Upload contract document
     */
    public function uploadDocument(Request $request, $id)
    {
        $type = $request->input('award_type', 'tender');

        try {
            // Validate the request
            $request->validate([
                'contract_document' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB max
            ]);

            $award = null;
            if ($type === 'rfq') {
                $award = RFQAward::findOrFail($id);
            } else {
                $award = TenderAward::findOrFail($id);
            }

            if ($request->hasFile('contract_document')) {
                $file = $request->file('contract_document');

                // Check if file is valid
                if (!$file->isValid()) {
                    throw new \Exception('Invalid file uploaded: ' . $file->getErrorMessage());
                }

                $originalName = $file->getClientOriginalName();
                $fileName = time() . '_' . $originalName;

                // Store the file
                $filePath = $file->storeAs('contracts/documents', $fileName, 'public');

                if (!$filePath) {
                    throw new \Exception('Failed to store file on disk');
                }

                // Handle existing documents - create a new structure to avoid JSON conflicts
                $existingConditions = $award->SpecialConditions;
                $documents = [];

                // Try to parse existing data as JSON (documents array)
                if (!empty($existingConditions)) {
                    $parsed = json_decode($existingConditions, true);
                    if (is_array($parsed)) {
                        $documents = $parsed;
                    } else {
                        // If it's not JSON, create new array and preserve original text in a special entry
                        $documents = [
                            [
                                'type' => 'Original Special Conditions',
                                'content' => $existingConditions,
                                'created_at' => now()->toISOString()
                            ]
                        ];
                    }
                }

                $newDoc = [
                    'type' => 'Contract Document',
                    'original_name' => $originalName,
                    'file_path' => $filePath,
                    'upload_date' => now()->toISOString(),
                    'uploaded_by' => Auth::id(),
                    'file_size' => $file->getSize()
                ];

                $documents[] = $newDoc;

                // Update the award
                $award->update([
                    'SpecialConditions' => json_encode($documents),
                    'ModifiedBy' => Auth::id(),
                ]);

                // Log successful upload
                \Log::info('Contract document uploaded successfully', [
                    'award_id' => $id,
                    'award_type' => $type,
                    'file_name' => $originalName,
                    'file_path' => $filePath,
                    'user_id' => Auth::id()
                ]);

                // Return JSON response for AJAX
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Document uploaded successfully.',
                        'document' => $newDoc
                    ]);
                }

                return redirect()->route('contracts.show', ['id' => $id, 'type' => $type])
                    ->with('success', 'Document uploaded successfully.');
            }

            throw new \Exception('No file was uploaded');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Contract document upload validation failed', [
                'award_id' => $id,
                'errors' => $e->errors(),
                'user_id' => Auth::id()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Contract document upload failed', [
                'award_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload failed: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to upload document: ' . $e->getMessage());
        }
    }

    /**
     * Add contract addendum
     */
    public function addAddendum(Request $request, $id)
    {
        $type = $request->input('award_type', 'tender');

        $request->validate([
            'addendum_title' => 'required|string|max:255',
            'addendum_description' => 'required|string',
            'effective_date' => 'required|date',
            'addendum_document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $award = null;
        if ($type === 'rfq') {
            $award = RFQAward::findOrFail($id);
        } else {
            $award = TenderAward::findOrFail($id);
        }

        // Handle document upload if provided
        $documentPath = null;
        if ($request->hasFile('addendum_document')) {
            $file = $request->file('addendum_document');
            $fileName = time() . '_addendum_' . $file->getClientOriginalName();
            $documentPath = $file->storeAs('contracts/addenda', $fileName, 'public');
        }

        // For now, store addendum info in the contract approval remarks
        // In a full implementation, you'd want a separate addenda table
        $existingRemarks = $award->ContractApprovalRemarks ?? '';
        $newAddendum = "\n\n--- ADDENDUM (" . $request->effective_date . ") ---\n";
        $newAddendum .= "Title: " . $request->addendum_title . "\n";
        $newAddendum .= "Description: " . $request->addendum_description . "\n";
        if ($documentPath) {
            $newAddendum .= "Document: " . $documentPath . "\n";
        }

        $award->update([
            'ContractApprovalRemarks' => $existingRemarks . $newAddendum,
            'ModifiedBy' => Auth::id(),
        ]);

        return redirect()->route('contracts.show', ['id' => $id, 'type' => $type])
            ->with('success', 'Addendum added successfully.');
    }
}
