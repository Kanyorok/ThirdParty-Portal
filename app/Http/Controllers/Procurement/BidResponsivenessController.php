<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\BidSubmission;
use App\Models\Procurement\Tender;
use App\Enums\Core\PermissionEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BidResponsivenessController extends Controller
{
    /**
     * Display responsiveness check page for a tender
     */
    public function index(Request $request)
    {
        $this->authorize(PermissionEnum::BidSubmissionRead);
        
        // Get tenders that have opened bids ready for responsiveness check
        $tenders = Tender::whereHas('submissions', function($query) {
                $query->whereNotNull('OpenedAt') // Only opened bids
                      ->whereIn('BidStatus', ['submitted', 'responsive', 'non-responsive']);
            })
            ->select('Id', 'TenderNo', 'Title', 'SubmissionDeadline', 'OpeningDate')
            ->orderBy('OpeningDate', 'desc')
            ->get();
        
        $selectedTender = null;
        $submissions = collect();
        
        if ($request->has('tender') && !empty($request->tender)) {
            $selectedTender = Tender::where('TenderNo', $request->tender)
                ->select('Id', 'TenderNo', 'Title', 'SubmissionDeadline', 'OpeningDate')
                ->first();
                
            if ($selectedTender) {
                $submissions = BidSubmission::forTender($selectedTender->TenderNo)
                    ->with(['supplier.thirdParty', 'openedByUser', 'responsivenessCheckedByUser'])
                    ->whereNotNull('OpenedAt') // Only opened bids
                    ->whereIn('BidStatus', ['submitted', 'responsive', 'non-responsive'])
                    ->orderBy('BidAmount')
                    ->get();
            }
        }
        
        return view('procurement.tendering.bidopeningandevaluation.responsivenesscheck.index', 
            compact('tenders', 'selectedTender', 'submissions'));
    }

    /**
     * Show detailed bid information for drill-down (documents, bidder details, etc.)
     */
    public function showBidDetails($bidId)
    {
        $this->authorize(PermissionEnum::BidSubmissionRead);
        
        $submission = BidSubmission::with([
            'supplier.supplierMaster.thirdParty.users', // Correct path to ThirdParty details and Users
            'tender', 
            'openedByUser', 
            'responsivenessCheckedByUser'
        ])->findOrFail($bidId);

        if (!$submission->isOpened()) {
            return response()->json([
                'success' => false,
                'message' => 'Bid is still sealed and cannot be viewed.'
            ], 403);
        }

        // Get document details (support both legacy and DMS-backed formats)
        $encryptedDocs = json_decode($submission->EncryptedDocuments, true) ?? [];

        // Collect DMS document IDs when present and fetch in one query
        $docIds = collect($encryptedDocs)
            ->map(fn($d) => $d['document_id'] ?? null)
            ->filter()
            ->values()
            ->all();

        $dmsDocs = [];
        if (!empty($docIds)) {
            $dmsDocs = \App\Models\DMS\Document::whereIn('DocumentId', $docIds)
                ->with('current')
                ->get()
                ->keyBy('DocumentId');
        }

        $documents = array_map(function($doc) use ($dmsDocs) {
            $documentId = $doc['document_id'] ?? null;
            $linked = $documentId && isset($dmsDocs[$documentId]) ? $dmsDocs[$documentId] : null;

            $name = $linked?->Name
                ?? ($doc['original_name'] ?? ($doc['original_filename'] ?? 'Unknown'));

            $sizeBytes = $linked?->current?->Size ?? ($doc['file_size'] ?? null);
            $uploadedAtVal = $linked?->getAttribute('CreatedOn');
            $uploadedAt = $uploadedAtVal instanceof \Carbon\Carbon
                ? $uploadedAtVal->format('d/m/Y H:i:s')
                : ($uploadedAtVal ?: ($doc['uploaded_at'] ?? null));

            return [
                'id' => $documentId ?? ($doc['id'] ?? 'unknown'),
                'filename' => $name,
                'size' => $sizeBytes !== null ? $this->formatFileSize($sizeBytes) : 'N/A',
                'uploaded_at' => $uploadedAt,
                'can_view' => true
            ];
        }, $encryptedDocs);

        // Get supplier details - fix relationship traversal
        $thirdParty = $submission->supplier->supplierMaster->thirdParty ?? null;
        $primaryUser = $thirdParty?->users->sortByDesc('CreatedOn')->first();
        
        $supplierDetails = [
            'supplier_name' => $submission->SupplierName,
            'supplier_id' => $submission->SupplierId,
            'third_party_name' => $thirdParty->ThirdPartyName ?? 'N/A',
            'trading_name' => $thirdParty->TradingName ?? 'N/A',
            'registration_number' => $thirdParty->RegistrationNumber ?? 'N/A',
            'contact_person' => $primaryUser?->fullName ?? 'N/A',
            'email' => $primaryUser?->Email ?? ($thirdParty->Email ?? 'N/A'),
            'phone' => $primaryUser?->Phone ?? ($thirdParty->Phone ?? 'N/A'),
            'address' => $thirdParty->PhysicalAddress ?? 'N/A'
        ];

        // Get responsiveness summary
        $responsivenessSummary = $submission->getResponsivenessSummary();
        
        // Default 'submitted_timely' to system check if not yet manually verified
        if ($responsivenessSummary['submitted_timely']['status'] === null) {
            $responsivenessSummary['submitted_timely']['status'] = $submission->ReceivedOnTime;
            $responsivenessSummary['submitted_timely']['remarks'] = 'Auto-detected from submission timestamp';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'submission_info' => [
                    'id' => $submission->Id,
                    'tender_ref' => $submission->TenderRef,
                    'tender_title' => $submission->tender->Title ?? 'N/A',
                    'bid_amount' => $submission->BidAmount,
                    'currency' => $submission->Currency,
                    'received_at' => $submission->ReceivedAt->format('d/m/Y H:i:s'),
                    'submission_source' => ucfirst($submission->SubmissionSource)
                ],
                'supplier_details' => $supplierDetails,
                'documents' => $documents, // Keep for backward compat if needed
                'documents_html' => view('partials.documents_summary', ['documents' => $dmsDocs])->render(),
                'responsiveness_summary' => $responsivenessSummary,
                'opening_details' => [
                    'opened_at' => $submission->OpenedAt?->format('d/m/Y H:i:s'),
                    'opened_by' => $submission->openedByUser?->Name ?? 'Unknown',
                    'ceremony_type' => ucfirst($submission->CeremonyType ?? 'Unknown'),
                    'read_out_summary' => $submission->ReadOutSummary
                ]
            ]
        ]);
    }

    /**
     * View document content (placeholder for future DMS integration)
     */
    public function viewDocument($bidId, $documentId)
    {
        $this->authorize(PermissionEnum::BidSubmissionRead);
        
        $submission = BidSubmission::findOrFail($bidId);

        if (!$submission->canAccessDocuments()) {
            return response()->json([
                'success' => false,
                'message' => 'Documents are not accessible yet.'
            ], 403);
        }

        // In a real implementation, this would decrypt and serve the document
        $encryptedDocs = json_decode($submission->EncryptedDocuments, true) ?? [];
        $document = collect($encryptedDocs)->firstWhere('id', $documentId);

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found.'
            ], 404);
        }

        // For now, return document metadata
        // TODO: Implement actual document decryption and viewing
        return response()->json([
            'success' => true,
            'message' => 'Document viewing will be implemented with DMS integration',
            'data' => [
                'document_info' => $document,
                'supplier' => $submission->SupplierName,
                'tender' => $submission->TenderRef,
                'note' => 'Document decryption and viewing requires DMS integration'
            ]
        ]);
    }

    /**
     * Perform detailed responsiveness check on a bid
     */
    public function checkResponsiveness(Request $request, $bidId)
    {
        $this->authorize(PermissionEnum::BidSubmissionWrite);
        
        $request->validate([
            'is_responsive' => 'required|boolean',
            'remarks' => 'required_if:is_responsive,false|nullable|string|max:1000',
        ]);
        
        $bid = BidSubmission::findOrFail($bidId);
        
        // Ensure bid is opened and eligible for responsiveness check
        if (!$bid->isOpened()) {
            return response()->json([
                'success' => false,
                'message' => 'Bid must be opened before responsiveness check.'
            ], 400);
        }
        
        DB::beginTransaction();
        
        try {
            $isResponsive = $request->boolean('is_responsive');
            
            if ($isResponsive) {
                $bid->markAsResponsive($request->remarks);
                $message = "Bid marked as RESPONSIVE for {$bid->SupplierName}";
            } else {
                $bid->markAsNonResponsive($request->remarks);
                $message = "Bid marked as NON-RESPONSIVE for {$bid->SupplierName}";
            }
            
            // Log the activity
            activity()
                ->performedOn($bid)
                ->causedBy(Auth::user())
                ->withProperties([
                    'action' => 'responsiveness_check',
                    'is_responsive' => $isResponsive,
                    'remarks' => $request->remarks
                ])
                ->log("Responsiveness check: {$message}");
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'bid_id' => $bid->Id,
                    'is_responsive' => $isResponsive,
                    'status' => $bid->BidStatus,
                    'checked_at' => $bid->ResponsivenessCheckedAt?->format('d/m/Y H:i:s')
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update responsiveness: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Perform detailed responsiveness check (aligned with t_BidResponsiveness structure)
     */
    public function detailedResponsivenessCheck(Request $request, $bidId)
    {
        $this->authorize(PermissionEnum::BidSubmissionWrite);
        
        $request->validate([
            'submitted_timely' => 'required|boolean',
            'has_mandatory_documents' => 'required|boolean',
            'is_eligible' => 'required|boolean',
            'timely_remarks' => 'nullable|string|max:500',
            'document_remarks' => 'nullable|string|max:500',
            'eligibility_remarks' => 'nullable|string|max:500',
            'overall_remarks' => 'nullable|string|max:1000',
        ]);

        $bid = BidSubmission::findOrFail($bidId);

        if (!$bid->isOpened()) {
            return response()->json([
                'success' => false,
                'message' => 'Bid must be opened before responsiveness check.'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $criteria = [
                'submitted_timely' => $request->boolean('submitted_timely'),
                'has_mandatory_documents' => $request->boolean('has_mandatory_documents'),
                'is_eligible' => $request->boolean('is_eligible'),
                'timely_remarks' => $request->timely_remarks,
                'document_remarks' => $request->document_remarks,
                'eligibility_remarks' => $request->eligibility_remarks
            ];

            $bid->performDetailedResponsivenessCheck($criteria, $request->overall_remarks, Auth::user());

            // Log detailed activity
            activity()
                ->performedOn($bid)
                ->causedBy(Auth::user())
                ->withProperties([
                    'action' => 'detailed_responsiveness_check',
                    'criteria' => $criteria,
                    'overall_remarks' => $request->overall_remarks,
                    'is_responsive' => $bid->IsResponsive
                ])
                ->log("Detailed responsiveness check completed for {$bid->SupplierName}");

            DB::commit();

            $message = $bid->IsResponsive 
                ? "Bid marked as RESPONSIVE for {$bid->SupplierName}" 
                : "Bid marked as NON-RESPONSIVE for {$bid->SupplierName}";

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'bid_id' => $bid->Id,
                    'responsiveness_summary' => $bid->getResponsivenessSummary()
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform detailed responsiveness check: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk responsiveness check for multiple bids
     */
    public function bulkCheck(Request $request)
    {
        $this->authorize(PermissionEnum::BidSubmissionWrite);
        
        $request->validate([
            'tender_ref' => 'required|exists:t_Tenders,TenderNo',
            'bid_checks' => 'required|array|min:1',
            'bid_checks.*.bid_id' => 'required|exists:t_BidSubmissions,Id',
            'bid_checks.*.is_responsive' => 'required|boolean',
            'bid_checks.*.remarks' => 'nullable|string|max:1000',
        ]);
        
        DB::beginTransaction();
        
        try {
            $responsiveCount = 0;
            $nonResponsiveCount = 0;
            
            foreach ($request->bid_checks as $check) {
                $bid = BidSubmission::findOrFail($check['bid_id']);
                
                if ($check['is_responsive']) {
                    $bid->markAsResponsive($check['remarks'] ?? null);
                    $responsiveCount++;
                } else {
                    $bid->markAsNonResponsive($check['remarks'] ?? 'Failed responsiveness criteria');
                    $nonResponsiveCount++;
                }
                
                activity()
                    ->performedOn($bid)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'action' => 'bulk_responsiveness_check',
                        'is_responsive' => $check['is_responsive'],
                        'remarks' => $check['remarks'] ?? null
                    ])
                    ->log("Bulk responsiveness check for {$bid->SupplierName}");
            }
            
            DB::commit();
            
            $message = "Bulk responsiveness check completed: {$responsiveCount} responsive, {$nonResponsiveCount} non-responsive";
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform bulk check: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get responsiveness criteria for a tender type
     */
    public function getCriteria(Request $request)
    {
        $tenderType = $request->get('tender_type', 'default');
        
        // Define responsiveness criteria based on tender type
        $criteria = [
            'goods' => [
                'Technical specifications compliance',
                'Valid tax compliance certificate',
                'Company registration documents',
                'Bid validity period (minimum 60 days)',
                'Delivery timeline compliance',
                'Required licenses and permits',
                'Financial capacity documents'
            ],
            'services' => [
                'Technical proposal completeness',
                'Methodology and approach clarity', 
                'Key personnel qualifications',
                'Company experience and references',
                'Valid professional licenses',
                'Insurance coverage proof',
                'Bid validity period compliance'
            ],
            'works' => [
                'Technical specifications compliance',
                'Construction methodology', 
                'Project timeline feasibility',
                'Equipment and machinery availability',
                'Key personnel qualifications',
                'Previous similar project experience',
                'Valid contractor registration',
                'Bid security/bond provision'
            ],
            'default' => [
                'All required documents submitted',
                'Bid submitted before deadline',
                'Bid validity period adequate',
                'Technical specifications met',
                'Financial documents complete',
                'Legal requirements satisfied'
            ]
        ];
        
        return response()->json([
            'criteria' => $criteria[$tenderType] ?? $criteria['default'],
            'tender_type' => $tenderType
        ]);
    }

    /**
     * Export responsiveness check report
     */
    public function exportReport(Request $request, $tenderRef)
    {
        $this->authorize(PermissionEnum::BidSubmissionRead);
        
        $tender = Tender::where('TenderNo', $tenderRef)->firstOrFail();
        $submissions = BidSubmission::forTender($tenderRef)
            ->with(['supplier.thirdParty'])
            ->whereIn('BidStatus', ['responsive', 'non-responsive'])
            ->get();
        
        $reportData = [
            'tender' => $tender,
            'submissions' => $submissions,
            'responsive_count' => $submissions->where('IsResponsive', true)->count(),
            'non_responsive_count' => $submissions->where('IsResponsive', false)->count(),
            'generated_at' => now(),
            'generated_by' => Auth::user()->name
        ];
        
        // Return JSON for now - can be enhanced to Excel/PDF export
        return response()->json($reportData);
    }

    /**
     * Helper method to format file sizes
     */
    private function formatFileSize($bytes)
    {
        if ($bytes == 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $pow = floor(log($bytes, 1024));
        
        return round($bytes / (1024 ** $pow), 2) . ' ' . $units[$pow];
    }
}
