<?php

namespace App\Http\Controllers\API\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\BidSubmission;
use App\Models\Procurement\Tender;
use App\Models\ThirdParies\Supplier;
use App\Models\Auth\User;
use App\Services\Procurement\EncryptedBidDocumentService;
use App\Enums\TenderStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BidSubmissionApiController extends Controller
{
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
            
            if (!$supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier not found for the provided third_party_id',
                ], 404);
            }

            // Check if tender is still open for submissions
            if (!$this->isTenderOpenForSubmissions($tender)) {
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
                // Store encrypted documents
                $encryptedDocs = EncryptedBidDocumentService::storeEncryptedBidDocuments(
                    new BidSubmission(), // Temporary instance for service
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
                    'timestamp' => now()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Document storage service error. Please contact system administrator.',
                    'error_code' => 'STORAGE_ERROR',
                    'debug_info' => [
                        'error_type' => get_class($e),
                        'storage_status' => is_writable(storage_path('app')) ? 'writable' : 'permission_error',
                        'timestamp' => now()->toISOString()
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
     * Get bid submissions for a supplier
     */
    public function getSupplierBids(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'third_party_id' => 'required|integer|exists:t_ThirdParties,Id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $supplier = $this->getSupplierByThirdPartyId($request->third_party_id);
            
            if (!$supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier not found',
                ], 404);
            }

            $submissions = BidSubmission::where('SupplierId', $supplier->Id)
                ->with(['submissionMode', 'createdByUser'])
                ->orderBy('ReceivedAt', 'desc')
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
        // Check tender status and submission deadline
        return $tender->Status === TenderStatusEnum::Published && 
               ($tender->SubmissionDeadline === null || now()->lte($tender->SubmissionDeadline));
    }

    /**
     * Get supplier by third party ID
     */
    private function getSupplierByThirdPartyId(int $thirdPartyId): ?Supplier
    {
        return Supplier::where('ThirdPartyID', $thirdPartyId)
            ->whereNull('DeletedOn')
            ->with('thirdParty')
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
        
        if (!$user) {
            // Fallback: get any user (even if soft deleted)
            $user = User::first();
        }
        
        if (!$user) {
            throw new \Exception('No users found in the system for bid processing');
        }
        
        return $user;
    }
}
