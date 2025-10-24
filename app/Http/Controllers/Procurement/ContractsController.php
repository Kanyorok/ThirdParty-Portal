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
        $query = TenderAward::with(['tender', 'winningSupplier.thirdParty'])
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
        $contract = TenderAward::with(['tender', 'winningSupplier.thirdParty'])
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
        $contracts = TenderAward::with(['tender', 'winningSupplier.thirdParty'])
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
     * Reject contract
     */
    public function rejectContract(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $award = TenderAward::findOrFail($id);

        // Set contract back to draft with rejection remarks
        $award->update([
            'ContractStatus' => 'Draft Created', // Return to draft for revision
            'ContractApprovalRemarks' => 'REJECTED: ' . $request->rejection_reason,
            'ContractApprovedBy' => null,
            'ContractApprovedOn' => null,
            'ModifiedBy' => Auth::id(),
        ]);

        // Log the rejection
        \Log::info('Contract rejected', [
            'award_id' => $id,
            'rejection_reason' => $request->rejection_reason,
            'rejected_by' => Auth::id()
        ]);

        return redirect()->route('contracts.approvalQueue')
            ->with('warning', 'Contract rejected and returned to draft status for revision.');
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

    /**
     * Submit contract for review
     */
    public function submitForReview(Request $request, $id)
    {
        $request->validate([
            'review_notes' => 'nullable|string|max:500',
        ]);

        $award = TenderAward::findOrFail($id);

        if ($award->ContractStatus !== 'Draft Created') {
            return redirect()->back()
                ->with('error', 'Only draft contracts can be submitted for review.');
        }

        $award->update([
            'ContractStatus' => 'Under Review',
            'ContractApprovalRemarks' => $request->review_notes,
            'ModifiedBy' => Auth::id(),
        ]);

        return redirect()->route('contracts.show', $id)
            ->with('success', 'Contract submitted for review successfully.');
    }

    /**
     * Upload contract document
     */
    public function uploadDocument(Request $request, $id)
    {
        try {
            // Validate the request
            $request->validate([
                'contract_document' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB max
            ]);

            $award = TenderAward::findOrFail($id);

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

                return redirect()->route('contracts.show', $id)
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
        $request->validate([
            'addendum_title' => 'required|string|max:255',
            'addendum_description' => 'required|string',
            'effective_date' => 'required|date',
            'addendum_document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $award = TenderAward::findOrFail($id);

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

        return redirect()->route('contracts.show', $id)
            ->with('success', 'Addendum added successfully.');
    }
}
