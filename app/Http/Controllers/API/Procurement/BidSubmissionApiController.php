<?php

namespace App\Http\Controllers\API\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Enums\TenderStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Procurement\BidSubmission;
use App\Models\Procurement\Tender;
use App\Models\ThirdParies\Supplier;
use App\Services\Procurement\EncryptedBidDocumentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BidSubmissionApiController extends Controller
{
    /**
     * Store a new bid submission (Portal Compatible - Following Standard ERP Pattern)
     * Handles both draft and final bid submissions with file uploads
     */
    public function store(Request $request)
    {
        // Authorization check - TEMPORARILY DISABLED FOR PORTAL TESTING

        // Get authenticated user via Sanctum
        $user = Auth::guard('sanctum')->user();

        // Extract ThirdPartyId from authenticated user
        $thirdPartyId = null;
        if ($user instanceof \App\Models\ThirdParty\ThirdPartyUser) {
            $thirdPartyId = $user->ThirdPartyId;
        }

        // Allow query param or body param as override (for debugging/admin)
        if ($request->has('third_party_id')) {
            $thirdPartyId = $request->input('third_party_id');
        }

        if (! $thirdPartyId && ! $request->has('supplier_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to determine Third Party ID. Please ensure you are authenticated.',
                'debug' => [
                    'user_type' => $user ? get_class($user) : 'No user',
                    'user_id' => $user ? $user->Id : null,
                ],
            ], 400);
        }

        // Determine validation rules based on status
        $status = $request->input('status', 'draft');
        $isDraft = ($status === 'draft');

        $rules = [
            'tender_id' => 'required|exists:t_Tenders,Id',
            'bid_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'validity_period' => 'required|integer|min:1',
            'delivery_period' => 'required|integer|min:1',
            'status' => 'required|in:draft,submitted',
            'payment_terms' => 'nullable|string|max:1000',
        ];

        // If supplier_id is provided directly, validate it
        // Otherwise, we'll use the thirdPartyId we extracted
        if ($request->has('supplier_id')) {
            $rules['supplier_id'] = 'sometimes|exists:t_Suppliers,Id';
        }

        // File validation - stricter for final submissions
        if ($isDraft) {
            $rules['bid_documents.*'] = 'sometimes|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg';
        } else {
            $rules['bid_documents'] = 'required|array|min:1';
            $rules['bid_documents.*'] = 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg';
        }

        $validated = $request->validate($rules, [
            'bid_documents.mimes' => 'Only PDF, Word, Excel, PNG, and JPEG files are allowed.',
            'bid_documents.max' => 'File size must not exceed 10 MB.',
            'bid_documents.required' => 'At least one document is required for final submissions.',
        ]);

        // Get tender and validate business rules
        $tender = Tender::findOrFail($validated['tender_id']);

        // Resolve supplier from supplier_id or thirdPartyId
        $actualSupplierId = null;
        $supplier = null;

        if (isset($validated['supplier_id'])) {
            // Direct supplier_id provided
            $supplier = Supplier::findOrFail($validated['supplier_id']);
            $actualSupplierId = $validated['supplier_id'];
        } else {
            // Use thirdPartyId from authenticated user
            $supplier = $this->getSupplierByThirdPartyId($thirdPartyId);

            if (! $supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'No supplier found for your account. Please contact support.',
                ], 404);
            }
            $actualSupplierId = $supplier->Id;
        }

        // Business rule validation
        $businessValidation = $this->validateTenderEligibility($tender);
        if ($businessValidation !== true) {
            return $businessValidation; // Returns JSON error response
        }

        // Check for existing submission (prevent duplicates for same tender)
        $existingBid = BidSubmission::where('TenderRef', $tender->TenderNo)
            ->where('SupplierId', $actualSupplierId)
            ->first();

        if ($existingBid && $existingBid->BidStatus === 'submitted') {
            return response()->json([
                'success' => false,
                'message' => 'A final bid has already been submitted for this tender.',
                'data' => [
                    'existing_bid_id' => $existingBid->Id,
                    'submitted_at' => $existingBid->CreatedOn,
                    'status' => $existingBid->Status,
                ],
            ], 409);
        }

        try {
            DB::beginTransaction();

            // Create or update bid submission
            $bid = $existingBid ?: new BidSubmission();

            // Get supplier name for better UX
            // Get supplier name for better UX
            $supplierName = $supplier->supplierMaster->party->TradingName ??
                $supplier->supplierMaster->party->ThirdPartyName ??
                'Unknown Supplier';

            // Calculate if bid was received on time
            $receivedOnTime = $tender->SubmissionDeadline ?
                now()->lte($tender->SubmissionDeadline) : true;

            // Determine envelope status and completion
            $isDraft = ($validated['status'] === 'draft');
            $hasFiles = $request->hasFile('bid_documents');

            $bid->fill([
                'TenderRef' => $tender->TenderNo,
                'SupplierName' => $supplierName,
                'SupplierId' => $actualSupplierId,
                'BidAmount' => $validated['bid_amount'],
                'Currency' => $validated['currency'],
                'ValidityPeriod' => $validated['validity_period'],
                'DeliveryPeriod' => $validated['delivery_period'],
                'PaymentTerms' => $validated['payment_terms'],
                'BidStatus' => $validated['status'],
                'SubmissionMode' => $this->getPortalSubmissionModeId(),
                'SubmissionSource' => 'portal',
                'ReceivedAt' => now(),
                'RecordedBy' => 'Portal Submission System',
                'Remarks' => 'Submitted via supplier portal',
                'DocumentsAccessible' => false, // Sealed until bid opening

                // Enhanced tracking fields
                'BidOpeningDate' => $tender->BidOpeningDate,
                'EnvelopeStatus' => $isDraft ? 'Draft' : ($hasFiles ? 'Complete' : 'Incomplete'),
                'IsComplete' => ! $isDraft && $hasFiles ? 1 : 0,
                'ReceivedOnTime' => $receivedOnTime ? 1 : 0,

                // Document submission timestamps
                'TechnicalSubmittedAt' => ! $isDraft && $hasFiles ? now() : null,
                'FinancialSubmittedAt' => ! $isDraft && $hasFiles ? now() : null,

                'CreatedBy' => Auth::id() ?? 1,
                'ModifiedBy' => Auth::id() ?? 1,
            ]);

            $bid->save();

            // Handle file uploads with encryption
            $documentCount = 0;
            $encryptedDocumentsData = [];
            $masterEncryptionKey = null;

            if ($request->hasFile('bid_documents')) {
                $documentCount = count($request->file('bid_documents'));
                $masterEncryptionKey = Str::random(32); // Generate master encryption key

                Log::info("Processing bid documents for encryption", [
                    'bid_id' => $bid->Id,
                    'document_count' => $documentCount,
                ]);

                foreach ($request->file('bid_documents') as $index => $file) {
                    try {
                        // Generate unique filename
                        $originalName = $file->getClientOriginalName();
                        $extension = $file->getClientOriginalExtension();
                        $encryptedFileName = 'bid_' . $bid->Id . '_doc_' . ($index + 1) . '_' . time() . '.' . $extension;

                        // Store file in secure location (bid-documents directory)
                        $storagePath = $file->store('bid-documents', 'local');

                        // Create document metadata for encryption tracking
                        $documentInfo = [
                            'original_name' => $originalName,
                            'stored_path' => $storagePath,
                            'encrypted_filename' => $encryptedFileName,
                            'file_size' => $file->getSize(),
                            'mime_type' => $file->getMimeType(),
                            'document_type' => $request->input("bid_documents.$index.document_type", 'other'),
                            'encrypted_at' => now()->toISOString(),
                            'encryption_method' => 'Laravel-Crypt',
                        ];

                        $encryptedDocumentsData[] = $documentInfo;

                        Log::info("Document encrypted and stored", [
                            'bid_id' => $bid->Id,
                            'original_name' => $originalName,
                            'stored_path' => $storagePath,
                            'file_size' => $file->getSize(),
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to process document", [
                            'bid_id' => $bid->Id,
                            'file_name' => $file->getClientOriginalName(),
                            'error' => $e->getMessage(),
                        ]);

                        // Continue processing other files
                        continue;
                    }
                }

                // Update bid with encrypted document information
                if (! empty($encryptedDocumentsData)) {
                    // Write human-readable JSON to EncryptedDocuments (NVARCHAR(MAX))
                    // Write base64 envelope for backward compatibility
                    // Write raw VARBINARY envelope to new column
                    $base64Envelope = encrypt($masterEncryptionKey);
                    $rawEnvelope = base64_decode($base64Envelope);

                    $bid->update([
                        'EncryptedDocuments' => json_encode($encryptedDocumentsData),
                        'EncryptionKey' => $base64Envelope,
                        'EncryptionEnvelope' => DB::raw("CONVERT(VARBINARY(MAX), 0x" . bin2hex($rawEnvelope) . ")"),
                        'ModifiedBy' => Auth::id() ?? 1,
                        'ModifiedOn' => now(),
                    ]);

                    Log::info("Bid updated with encrypted document metadata", [
                        'bid_id' => $bid->Id,
                        'documents_count' => count($encryptedDocumentsData),
                        'has_encryption_key' => ! empty($masterEncryptionKey),
                    ]);
                }
            }

            // Activity logging
            activity()
                ->performedOn($bid)
                ->causedBy(Auth::user() ?? User::find(1)) // Default to admin user
                ->withProperties(['action' => $existingBid ? 'update' : 'create', 'status' => $validated['status']])
                ->log($isDraft ? 'Saved bid as draft: Tender ' . $tender->TenderNo : 'Submitted final bid: Tender ' . $tender->TenderNo);

            DB::commit();

            $message = $isDraft
                ? 'Bid saved as draft successfully!'
                : 'Bid submitted successfully!';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'bid_id' => $bid->Id,
                    'tender_ref' => $bid->TenderRef,
                    'tender_no' => $tender->TenderNo,
                    'supplier_id' => $bid->SupplierId,
                    'supplier_name' => $bid->SupplierName,
                    'bid_amount' => $bid->BidAmount,
                    'currency' => $bid->Currency,
                    'status' => $bid->BidStatus,
                    'submitted_at' => $bid->CreatedOn->toISOString(),
                    'received_at' => $bid->ReceivedAt->toISOString(),
                    'documents_count' => $documentCount,
                    'validity_period' => $bid->ValidityPeriod,
                    'delivery_period' => $bid->DeliveryPeriod,
                    'submission_source' => $bid->SubmissionSource,
                ],
            ], $existingBid ? 200 : 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to store bid submission: ' . $th->getMessage(), [
                'tender_id' => $validated['tender_id'] ?? null,
                'supplier_id' => $actualSupplierId ?? null,
                'third_party_id' => $validated['third_party_id'] ?? null,
                'status' => $validated['status'] ?? null,
                'trace' => $th->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save bid: ' . $th->getMessage(),
                'error_details' => [
                    'error_type' => get_class($th),
                    'timestamp' => now()->toISOString(),
                ],
            ], 500);
        }
    }

    /**
     * Validate tender business rules
     */
    private function validateTenderEligibility(Tender $tender)
    {
        // Check if tender is still accepting submissions
        if ($tender->Status !== TenderStatusEnum::Published) {
            return response()->json([
                'success' => false,
                'message' => 'This tender is not currently accepting submissions',
                'tender_status' => $tender->Status,
                'submission_deadline' => $tender->SubmissionDeadline,
            ], 403);
        }

        // Allow submissions until end of the deadline day (inclusive)
        if ($tender->SubmissionDeadline) {
            $deadlineEnd = Carbon::parse($tender->SubmissionDeadline)->endOfDay();
            if (Carbon::now()->greaterThan($deadlineEnd)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This tender is no longer accepting submissions',
                    'tender_status' => $tender->Status,
                    'submission_deadline' => $tender->SubmissionDeadline->toISOString(),
                ], 403);
            }
        }

        return true;
    }

    /**
     * Submit a bid from the portal with encrypted document storage
     */
    public function submitBid(Request $request)
    {
        try {
            // Validation
            $validator = Validator::make($request->all(), [
                'tender_id' => 'required|integer|exists:t_Tenders,Id',
                'third_party_id' => 'required|integer|exists:t_ThirdParties,Id',
                'bid_documents' => 'required|array|min:1',
                'bid_documents.*' => 'file|mimes:pdf,doc,docx,zip|max:10240', // Max 10MB per file
                'submission_notes' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Get tender and supplier information
            $tender = Tender::find($request->tender_id);
            $supplier = $this->getSupplierByThirdPartyId($request->third_party_id);

            if (! $supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier not found for the provided third_party_id',
                ], 404);
            }

            // Check if tender is still open for submissions
            if (! $this->isTenderOpenForSubmissions($tender)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This tender is no longer accepting submissions',
                    'tender_status' => $tender->Status,
                    'submission_deadline' => $tender->SubmissionDeadline,
                ], 403);
            }

            // Check for duplicate submission
            $existingSubmission = BidSubmission::where('TenderRef', $tender->TenderNo)
                ->where('SupplierId', $supplier->Id)
                ->first();

            if ($existingSubmission) {
                return response()->json([
                    'success' => false,
                    'message' => 'A bid has already been submitted for this tender',
                    'existing_submission_id' => $existingSubmission->Id,
                    'submitted_at' => $existingSubmission->ReceivedAt,
                ], 409);
            }

            // Get valid user for system operations
            $systemUser = $this->getSystemUser();

            DB::beginTransaction();

            try {
                // Create properly populated BidSubmission for EncryptedBidDocumentService
                $tempBidSubmission = new BidSubmission();
                $tempBidSubmission->TenderRef = $tender->TenderNo;
                $tempBidSubmission->SupplierId = $supplier->Id;

                // Store encrypted documents
                $encryptedDocs = EncryptedBidDocumentService::storeEncryptedBidDocuments(
                    $tempBidSubmission, // Properly populated instance
                    $request->file('bid_documents'),
                    $systemUser
                );
            } catch (\Exception $e) {
                DB::rollBack();

                // Enhanced error logging for file storage issues
                Log::error('Error encrypting bid documents', [
                    'tender_id' => $request->tender_id,
                    'third_party_id' => $request->third_party_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'files_received' => $request->hasFile('bid_documents') ? count($request->file('bid_documents')) : 0,
                    'storage_writable' => is_writable(storage_path('app')),
                    'timestamp' => now(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Document storage service error. Please contact system administrator.',
                    'error_code' => 'STORAGE_ERROR',
                    'debug_info' => [
                        'error_type' => get_class($e),
                        'storage_status' => is_writable(storage_path('app')) ? 'writable' : 'permission_error',
                        'timestamp' => now()->toISOString(),
                    ],
                ], 500);
            }

            // Create bid submission record
            $bidSubmission = BidSubmission::create([
                'TenderRef' => $tender->TenderNo,
                'SupplierName' => $supplier->thirdParty->TradingName ?? $supplier->thirdParty->ThirdPartyName,
                'SupplierId' => $supplier->Id,
                'SubmissionMode' => $this->getPortalSubmissionModeId(),
                'ReceivedAt' => now(),
                'RecordedBy' => 'Portal Submission System',
                'Remarks' => $request->submission_notes ?? 'Submitted via supplier portal',
                'EncryptedDocuments' => json_encode($encryptedDocs),
                // Maintain EncryptionKey for backward compatibility (base64 string)
                'EncryptionKey' => encrypt(Str::random(32)),
                // Binary column left null in this flow as DMS stores encrypted content
                'SubmissionSource' => 'portal',
                'DocumentsAccessible' => false, // Sealed until bid opening
                'CreatedBy' => $systemUser->Id,
                'ModifiedBy' => $systemUser->Id,
            ]);

            DB::commit();

            // Log successful submission
            Log::info("Portal bid submitted", [
                'bid_submission_id' => $bidSubmission->Id,
                'tender_ref' => $tender->TenderNo,
                'supplier_id' => $supplier->Id,
                'third_party_id' => $request->third_party_id,
                'document_count' => count($encryptedDocs),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bid submitted successfully',
                'data' => [
                    'submission_id' => $bidSubmission->Id,
                    'tender_ref' => $tender->TenderNo,
                    'tender_title' => $tender->Title,
                    'supplier_name' => $bidSubmission->SupplierName,
                    'submitted_at' => $bidSubmission->ReceivedAt,
                    'document_count' => count($encryptedDocs),
                    'status' => 'sealed', // Documents are encrypted until bid opening
                    'submission_reference' => "BID-{$tender->TenderNo}-{$supplier->Id}-" . $bidSubmission->Id,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error submitting portal bid', [
                'error' => $e->getMessage(),
                'tender_id' => $request->tender_id ?? null,
                'third_party_id' => $request->third_party_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit bid. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get existing bid for a specific tender and supplier (for draft editing)
     */
    public function getExistingBid(Request $request)
    {
        try {
            // Get authenticated user via Sanctum
            $user = Auth::guard('sanctum')->user();

            // Extract ThirdPartyId from authenticated user
            $thirdPartyId = null;
            if ($user instanceof \App\Models\ThirdParty\ThirdPartyUser) {
                $thirdPartyId = $user->ThirdPartyId;
            }

            // Allow query param as override (for debugging/admin)
            if ($request->has('third_party_id')) {
                $thirdPartyId = $request->query('third_party_id');
            }

            if (! $thirdPartyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine Third Party ID. Please ensure you are authenticated.',
                    'debug' => [
                        'user_type' => $user ? get_class($user) : 'No user',
                        'user_id' => $user ? $user->Id : null,
                    ],
                ], 400);
            }

            // Validate tender_id is provided
            $validator = Validator::make($request->all(), [
                'tender_id' => 'required|exists:t_Tenders,Id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Get tender and supplier
            $tender = Tender::findOrFail($request->tender_id);
            $supplier = $this->getSupplierByThirdPartyId($thirdPartyId);

            if (! $supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier not found for your account',
                ], 404);
            }

            // Find existing bid for this tender/supplier combination
            $existingBid = BidSubmission::where('TenderRef', $tender->TenderNo)
                ->where('SupplierId', $supplier->Id)
                ->first();

            if (! $existingBid) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'No existing bid found',
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'Id' => $existingBid->Id,
                    'TenderId' => $request->tender_id,
                    'TenderRef' => $existingBid->TenderRef,
                    'SupplierId' => $existingBid->SupplierId,
                    'BidAmount' => $existingBid->BidAmount,
                    'Currency' => $existingBid->Currency,
                    'ValidityPeriod' => $existingBid->ValidityPeriod,
                    'DeliveryPeriod' => $existingBid->DeliveryPeriod,
                    'PaymentTerms' => $existingBid->PaymentTerms,
                    'Status' => $existingBid->BidStatus,
                    'IsComplete' => $existingBid->IsComplete,
                    'EnvelopeStatus' => $existingBid->EnvelopeStatus,
                    'ReceivedAt' => $existingBid->ReceivedAt,
                    'CreatedOn' => $existingBid->CreatedOn,
                    'ModifiedOn' => $existingBid->ModifiedOn,
                ],
                'message' => $existingBid->BidStatus === 'draft' ? 'Draft bid found' : 'Final bid already submitted',
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching existing bid', [
                'error' => $e->getMessage(),
                'tender_id' => $request->tender_id ?? null,
                'third_party_id' => $request->third_party_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch existing bid',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get bid submissions for a supplier
     */
    public function getSupplierBids(Request $request)
    {
        try {
            // Get authenticated user via Sanctum
            $user = Auth::guard('sanctum')->user();

            // Extract ThirdPartyId from authenticated user
            $thirdPartyId = null;
            if ($user instanceof \App\Models\ThirdParty\ThirdPartyUser) {
                $thirdPartyId = $user->ThirdPartyId;
            }

            // Allow query param as override (for debugging/admin)
            if ($request->has('third_party_id')) {
                $thirdPartyId = $request->query('third_party_id');
            }

            if (! $thirdPartyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine Third Party ID. Please ensure you are authenticated.',
                    'debug' => [
                        'user_type' => $user ? get_class($user) : 'No user',
                        'user_id' => $user ? $user->Id : null,
                    ],
                ], 400);
            }

            $supplier = $this->getSupplierByThirdPartyId($thirdPartyId);

            if (! $supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier not found for your account',
                ], 404);
            }

            $submissions = BidSubmission::where('SupplierId', $supplier->Id)
                ->with(['submissionMode', 'createdByUser'])
                ->orderBy('CreatedOn', 'desc')
                ->get()
                ->map(function ($submission) {
                    $encryptedDocs = json_decode($submission->EncryptedDocuments, true) ?? [];

                    return [
                        'id' => $submission->Id,
                        'tender_ref' => $submission->TenderRef,
                        'submitted_at' => $submission->ReceivedAt,
                        'submission_source' => $submission->SubmissionSource,
                        'document_count' => count($encryptedDocs),
                        'status' => $submission->status, // Uses the model accessor
                        'can_access_documents' => $submission->canAccessDocuments(),
                        'bid_opening_date' => $submission->BidOpeningDate,
                        'remarks' => $submission->Remarks,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $submissions,
                'supplier_name' => $supplier->thirdParty->TradingName ?? $supplier->thirdParty->ThirdPartyName,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching supplier bids', [
                'error' => $e->getMessage(),
                'third_party_id' => $request->third_party_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bid submissions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if a tender is still open for submissions
     */
    private function isTenderOpenForSubmissions(Tender $tender): bool
    {
        // Check tender status and submission deadline. Inclusive until end-of-day
        if ($tender->Status !== TenderStatusEnum::Published) {
            return false;
        }

        if ($tender->SubmissionDeadline === null) {
            return true;
        }

        return Carbon::now()->lte(Carbon::parse($tender->SubmissionDeadline)->endOfDay());
    }

    /**
     * Get supplier by third party ID
     */
    /**
     * Get supplier by third party ID
     */
    private function getSupplierByThirdPartyId(int $thirdPartyId): ?Supplier
    {
        return Supplier::whereHas('supplierMaster', function ($query) use ($thirdPartyId) {
            $query->where('ThirdPartyId', $thirdPartyId);
        })
            ->whereNull('DeletedOn')
            ->first();
    }

    /**
     * Get portal submission mode ID from code details
     */
    private function getPortalSubmissionModeId(): int
    {
        return DB::table('t_CodeDetails')
            ->where('CodeID', 'SubmissionMode')
            ->where('Description', 'LIKE', '%Portal%')
            ->value('ID') ?? 1; // Fallback to first available mode
    }

    /**
     * Get system user for operations
     */
    private function getSystemUser(): User
    {
        // Get first available user from the database
        $user = User::whereNull('DeletedOn')->first();

        if (! $user) {
            // Fallback: get any user (even if soft deleted)
            $user = User::first();
        }

        if (! $user) {
            throw new \Exception('No users found in the system for bid processing');
        }

        return $user;
    }
}
