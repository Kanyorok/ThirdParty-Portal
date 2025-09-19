<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\BidSubmission;
use App\Models\Procurement\Tender;
use App\Services\Procurement\EncryptedBidDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenderOpeningController extends Controller
{
    /**
     * Display tenders ready for opening ceremony (opening date <= today)
     */
    public function index()
    {
        // Get tenders ready for opening based on opening date
        $tenders = Tender::whereHas('submissions', function($query) {
                $query->where('BidStatus', 'submitted');
            })
            ->where('OpeningDate', '<=', now()) // Only tenders where opening date has passed
            ->where('SubmissionDeadline', '<=', now()) // Ensure submission deadline has also passed
            ->select('Id', 'TenderNo', 'Title', 'Status', 'OpeningDate', 'SubmissionDeadline')
            ->orderBy('OpeningDate', 'desc')
            ->get();
        
        $data = false;
        return view('procurement.tendering.bidopeningandevaluation.opening.index', compact('tenders', 'data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('procurement.tendering.bidopeningandevaluation.opening.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified tender opening ceremony interface
     */
    public function show(string $id)
    {
        // Get the tender details
        $tender = Tender::where('TenderNo', $id)
            ->select('Id', 'TenderNo', 'Title', 'Status', 'OpeningDate', 'SubmissionDeadline')
            ->firstOrFail();
        
        // Check if tender is ready for opening
        if ($tender->OpeningDate > now()) {
            return redirect()->back()->with('error', 'Tender opening ceremony cannot start before the scheduled opening date: ' . $tender->OpeningDate->format('d/m/Y H:i'));
        }

        // Get all submissions for this tender with enhanced details
        $submissions = BidSubmission::where('TenderRef', $id)
            ->with(['createdByUser', 'modifiedByUser', 'submissionMode', 'supplier.thirdParty', 'openedByUser'])
            ->select('Id', 'SupplierName', 'SupplierId', 'SubmissionMode', 'ReceivedAt', 'CreatedBy', 'Remarks', 'ModifiedBy', 'SubmissionSource', 'DocumentsAccessible', 'BidOpeningDate', 'EncryptedDocuments', 'BidAmount', 'Currency', 'BidStatus', 'OpenedAt', 'OpenedBy', 'ValidityPeriod', 'DeliveryPeriod')
            ->orderBy('ReceivedAt')
            ->get()
            ->map(function ($submission) {
                $encryptedDocs = json_decode($submission->EncryptedDocuments, true) ?? [];
                $submission->document_count = count($encryptedDocs);
                $submission->status_badge = $this->getStatusBadge($submission);
                $submission->has_bid_security = $this->checkBidSecurity($encryptedDocs);
                $submission->submission_timely = $submission->ReceivedAt <= $submission->BidOpeningDate;
                return $submission;
            });

        // Get other tenders for dropdown
        $tenders = Tender::whereHas('submissions', function($query) {
                $query->where('BidStatus', 'submitted');
            })
            ->where('OpeningDate', '<=', now())
            ->where('SubmissionDeadline', '<=', now())
            ->select('Id', 'TenderNo', 'Title', 'Status', 'OpeningDate')
            ->orderBy('OpeningDate', 'desc')
            ->get();
            
        // Determine ceremony status
        $ceremonyStatus = $this->getCeremonyStatus($submissions);
        $data = true;
        
        return view('procurement.tendering.bidopeningandevaluation.opening.index', 
            compact('tender', 'tenders', 'submissions', 'data', 'ceremonyStatus'));
    }

    /**
     * Initialize PUBLIC/RECORDED bid opening ceremony 
     */
    public function startCeremony(Request $request)
    {
        $request->validate([
            'tender_ref' => 'required|string|exists:t_Tenders,TenderNo',
            'ceremony_notes' => 'nullable|string|max:1000',
            'officers_present' => 'nullable|string|max:500',
            'opening_type' => 'required|in:public,recorded',
        ]);

        $tender = Tender::where('TenderNo', $request->tender_ref)->firstOrFail();
        
        // Validate ceremony can be started
        if ($tender->OpeningDate > now()) {
            return redirect()->back()->with('error', 'Cannot start ceremony before scheduled opening date.');
        }

        $submissions = BidSubmission::where('TenderRef', $request->tender_ref)
            ->where('BidStatus', 'submitted')
            ->with(['supplier.thirdParty'])
            ->get();

        if ($submissions->isEmpty()) {
            return redirect()->back()->with('error', 'No submitted bids found for this tender.');
        }

        try {
            DB::beginTransaction();
            
            // Initialize ceremony (but don't open bids yet - that will be done individually)
            $tender->update([
                'OpeningDate' => now(), // Mark ceremony as started
                'Status' => 'opening_in_progress' // You may need to add this status
            ]);

            // Log ceremony initialization
            activity()
                ->performedOn($tender)
                ->causedBy($request->user())
                ->withProperties([
                    'action' => 'ceremony_initialized',
                    'ceremony_type' => $request->opening_type,
                    'officers_present' => $request->officers_present,
                    'ceremony_notes' => $request->ceremony_notes,
                    'total_bids' => $submissions->count()
                ])
                ->log("OPENING CEREMONY INITIALIZED: {$tender->TenderNo} - {$request->opening_type} ceremony with {$submissions->count()} bids");

            DB::commit();

            return redirect()->back()->with('success', 
                "🎉 Opening ceremony initialized! You can now open individual bids one by one.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error initializing opening ceremony', [
                'tender_ref' => $request->tender_ref,
                'user_id' => $request->user()->Id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to initialize ceremony: ' . $e->getMessage());
        }
    }

    /**
     * Open an individual bid during ceremony
     */
    public function openIndividualBid(Request $request, $submissionId)
    {
        try {
            $submission = BidSubmission::with(['supplier.thirdParty', 'tender'])->findOrFail($submissionId);
            
            // Validate ceremony is in progress
            if (!$submission->tender || !$submission->tender->OpeningDate || $submission->tender->OpeningDate > now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Opening ceremony has not been started for this tender.'
                ], 400);
            }

            // Check if bid is already opened
            if ($submission->OpenedAt) {
                return response()->json([
                    'success' => false,
                    'message' => 'This bid has already been opened.'
                ], 400);
            }

            DB::beginTransaction();

            // READ OUT BID BASICS (Public/Recorded Opening)
            $bidDetails = $this->readOutBidBasics($submission);
            
            // Get ceremony details from tender's activity log or session
            $ceremonyDetails = [
                'ceremony_type' => 'public', // You might want to store this in tender or get from session
                'read_out_summary' => $bidDetails['read_out_summary'],
                'bid_security_present' => $bidDetails['has_bid_security'],
                'received_on_time' => $bidDetails['received_on_time'] === 'YES'
            ];
            
            // Mark bid as opened
            $submission->markAsOpened($request->user(), $ceremonyDetails);

            // Log individual bid opening
            activity()
                ->performedOn($submission)
                ->causedBy($request->user())
                ->withProperties([
                    'action' => 'individual_bid_opening',
                    'bid_details' => $bidDetails,
                    'opened_at' => now()
                ])
                ->log("BID OPENED: {$submission->SupplierName} - {$bidDetails['read_out_summary']}");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bid opened successfully!',
                'data' => [
                    'bid_id' => $submission->Id,
                    'supplier_name' => $submission->SupplierName,
                    'read_out_summary' => $bidDetails['read_out_summary'],
                    'bid_details' => $bidDetails,
                    'opened_at' => $submission->fresh()->OpenedAt->format('d/m/Y H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error opening individual bid', [
                'submission_id' => $submissionId,
                'user_id' => $request->user()->Id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to open bid: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show detailed bid information (for "View Details" button)
     */
    public function showBidDetails($submissionId)
    {
        try {
            $submission = BidSubmission::with(['supplier.thirdParty', 'tender', 'openedByUser'])
                ->findOrFail($submissionId);

            if (!$submission->OpenedAt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bid is still sealed and cannot be viewed.'
                ], 403);
            }

            $encryptedDocs = json_decode($submission->EncryptedDocuments, true) ?? [];
            
            $bidDetails = [
                'submission_info' => [
                    'id' => $submission->Id,
                    'supplier_name' => $submission->SupplierName,
                    'tender_ref' => $submission->TenderRef,
                    'tender_title' => $submission->tender->Title ?? 'N/A'
                ],
                'bid_details' => [
                    'bid_amount' => $submission->BidAmount,
                    'currency' => $submission->Currency,
                    'validity_period' => $submission->ValidityPeriod . ' days',
                    'delivery_period' => $submission->DeliveryPeriod . ' days',
                    'payment_terms' => $submission->PaymentTerms,
                    'bid_status' => $submission->BidStatus
                ],
                'submission_details' => [
                    'received_at' => $submission->ReceivedAt->format('d/m/Y H:i:s'),
                    'submission_source' => ucfirst($submission->SubmissionSource),
                    'document_count' => count($encryptedDocs),
                    'bid_security_present' => $submission->BidSecurityPresent,
                    'received_on_time' => $submission->ReceivedOnTime
                ],
                'opening_details' => [
                    'opened_at' => $submission->OpenedAt->format('d/m/Y H:i:s'),
                    'opened_by' => $submission->openedByUser->name ?? 'Unknown',
                    'ceremony_type' => ucfirst($submission->CeremonyType ?? 'Unknown'),
                    'read_out_summary' => $submission->ReadOutSummary
                ],
                'documents' => array_map(function($doc) {
                    return [
                        'filename' => $doc['original_filename'] ?? 'Unknown',
                        'size' => $this->formatFileSize($doc['file_size'] ?? 0),
                        'uploaded_at' => $doc['uploaded_at'] ?? null
                    ];
                }, $encryptedDocs)
            ];

            return response()->json([
                'success' => true,
                'data' => $bidDetails
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load bid details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show read-out summary (for "Read Out" button)
     */
    public function showReadOutSummary($submissionId)
    {
        try {
            $submission = BidSubmission::with(['tender', 'openedByUser'])->findOrFail($submissionId);

            if (!$submission->OpenedAt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bid is still sealed. No read-out available.'
                ], 403);
            }

            $readOutData = [
                'tender_info' => [
                    'tender_no' => $submission->TenderRef,
                    'tender_title' => $submission->tender->Title ?? 'N/A'
                ],
                'ceremony_info' => [
                    'opened_at' => $submission->OpenedAt->format('d/m/Y H:i:s'),
                    'opened_by' => $submission->openedByUser->name ?? 'Unknown',
                    'ceremony_type' => ucfirst($submission->CeremonyType ?? 'Public'),
                    'officers_present' => $submission->OfficersPresent
                ],
                'public_read_out' => $submission->ReadOutSummary,
                'read_out_components' => [
                    'supplier_name' => $submission->SupplierName,
                    'bid_amount' => $submission->Currency . ' ' . number_format($submission->BidAmount, 2),
                    'validity_period' => $submission->ValidityPeriod . ' days',
                    'delivery_period' => $submission->DeliveryPeriod . ' days',
                    'bid_security' => $submission->BidSecurityPresent ? 'Present' : 'Not Found',
                    'received_status' => $submission->ReceivedOnTime ? 'On Time' : 'Late',
                    'document_count' => count(json_decode($submission->EncryptedDocuments, true) ?? []) . ' files'
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $readOutData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load read-out summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete the opening ceremony
     */
    public function completeCeremony(Request $request, $tenderRef)
    {
        try {
            $tender = Tender::where('TenderNo', $tenderRef)->firstOrFail();
            $submissions = BidSubmission::where('TenderRef', $tenderRef)->get();
            
            $openedCount = $submissions->whereNotNull('OpenedAt')->count();
            $totalCount = $submissions->count();
            
            if ($openedCount < $totalCount) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot complete ceremony. {$openedCount} of {$totalCount} bids have been opened. Please open all bids first."
                ], 400);
            }

            // Update tender status to indicate ceremony completion
            $tender->update([
                'Status' => 'opened' // Or your appropriate status
            ]);

            // Log ceremony completion
            activity()
                ->performedOn($tender)
                ->causedBy($request->user())
                ->withProperties([
                    'action' => 'ceremony_completed',
                    'total_bids_opened' => $openedCount,
                    'completed_at' => now()
                ])
                ->log("OPENING CEREMONY COMPLETED: {$tender->TenderNo} - All {$openedCount} bids opened");

            return response()->json([
                'success' => true,
                'message' => "🎉 Opening ceremony completed! All {$openedCount} bids have been opened and are ready for responsiveness check.",
                'data' => [
                    'tender_ref' => $tenderRef,
                    'opened_bids' => $openedCount,
                    'completed_at' => now()->format('d/m/Y H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete ceremony: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Access decrypted documents for a specific submission
     */
    public function accessDocuments(Request $request, $submissionId)
    {
        try {
            $submission = BidSubmission::findOrFail($submissionId);

            if (!$submission->canAccessDocuments()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documents are sealed until bid opening ceremony starts.',
                ], 403);
            }

            $decryptedDocs = EncryptedBidDocumentService::decryptBidDocuments(
                $submission,
                $request->user()
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'submission_id' => $submission->Id,
                    'tender_ref' => $submission->TenderRef,
                    'supplier_name' => $submission->SupplierName,
                    'documents' => $decryptedDocs,
                    'ceremony_started' => $submission->isBidOpeningCeremonyStarted(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error accessing bid documents', [
                'submission_id' => $submissionId,
                'user_id' => $request->user()->Id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to access documents: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a specific decrypted document
     */
    public function downloadDocument(Request $request, $submissionId, $documentIndex)
    {
        try {
            $submission = BidSubmission::findOrFail($submissionId);

            if (!$submission->canAccessDocuments()) {
                abort(403, 'Documents are sealed until bid opening ceremony starts.');
            }

            $decryptedDocs = EncryptedBidDocumentService::decryptBidDocuments(
                $submission,
                $request->user()
            );

            if (!isset($decryptedDocs[$documentIndex])) {
                abort(404, 'Document not found.');
            }

            $document = $decryptedDocs[$documentIndex];

            return response($document['content'])
                ->header('Content-Type', $document['mime_type'])
                ->header('Content-Disposition', 'attachment; filename="' . $document['name'] . '"')
                ->header('Content-Length', $document['size']);

        } catch (\Exception $e) {
            Log::error('Error downloading bid document', [
                'submission_id' => $submissionId,
                'document_index' => $documentIndex,
                'user_id' => $request->user()->Id,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Failed to download document.');
        }
    }

    /**
     * READ OUT BID BASICS during public/recorded opening
     * This is the core of public bid opening - reading basic details without judgments
     */
    private function readOutBidBasics($bid): array
    {
        $encryptedDocs = json_decode($bid->EncryptedDocuments, true) ?? [];
        
        $bidDetails = [
            'supplier_name' => $bid->SupplierName,
            'bid_amount' => $bid->BidAmount,
            'currency' => $bid->Currency,
            'validity_period' => $bid->ValidityPeriod . ' days',
            'delivery_period' => $bid->DeliveryPeriod . ' days',
            'submission_time' => $bid->ReceivedAt->format('d/m/Y H:i:s'),
            'document_count' => count($encryptedDocs),
            'has_bid_security' => $this->checkBidSecurity($encryptedDocs),
            'envelope_sealed' => true, // Always true at opening
            'received_on_time' => $bid->ReceivedAt <= $bid->BidOpeningDate ? 'YES' : 'NO',
            'submission_method' => $bid->SubmissionSource === 'portal' ? 'Online Portal' : 'Manual Submission'
        ];
        
        // Create read-out summary for public announcement
        $readOutSummary = "Bidder: {$bidDetails['supplier_name']}, ";
        
        // Only include price if allowed (you can make this configurable)
        if ($this->isPriceDisclosureAllowed($bid)) {
            $readOutSummary .= "Amount: {$bidDetails['currency']} " . number_format($bidDetails['bid_amount'], 2) . ", ";
        }
        
        $readOutSummary .= "Documents: {$bidDetails['document_count']} files, ";
        $readOutSummary .= "Bid Security: " . ($bidDetails['has_bid_security'] ? 'Present' : 'Not Found') . ", ";
        $readOutSummary .= "Received: {$bidDetails['received_on_time']} (on time), ";
        $readOutSummary .= "Envelope: Sealed";
        
        $bidDetails['read_out_summary'] = $readOutSummary;
        
        return $bidDetails;
    }

    /**
     * Check if bid security document is present
     */
    private function checkBidSecurity($encryptedDocs): bool
    {
        foreach ($encryptedDocs as $doc) {
            $filename = strtolower($doc['original_filename'] ?? '');
            if (str_contains($filename, 'security') || 
                str_contains($filename, 'bond') || 
                str_contains($filename, 'guarantee')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine if price disclosure is allowed during opening
     */
    private function isPriceDisclosureAllowed($bid): bool
    {
        // This can be made configurable based on tender type or settings
        // For now, allow price disclosure for all tenders
        return true;
    }

    /**
     * Format file size for display
     */
    private function formatFileSize($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Get ceremony status for a set of submissions
     */
    private function getCeremonyStatus($submissions): array
    {
        $submittedCount = $submissions->where('BidStatus', 'submitted')->count();
        $openedCount = $submissions->whereNotNull('OpenedAt')->count();
        $totalCount = $submissions->count();

        if ($submittedCount === 0) {
            return [
                'status' => 'completed',
                'message' => 'All bids have been opened and processed',
                'can_start' => false,
                'submitted_count' => $submittedCount,
                'opened_count' => $openedCount,
                'total_count' => $totalCount
            ];
        }

        if ($openedCount === 0) {
            return [
                'status' => 'ready',
                'message' => "{$submittedCount} bids ready for public opening ceremony",
                'can_start' => true,
                'submitted_count' => $submittedCount,
                'opened_count' => $openedCount,
                'total_count' => $totalCount
            ];
        }

        if ($openedCount < $totalCount) {
            return [
                'status' => 'partial',
                'message' => "{$openedCount} of {$totalCount} bids opened",
                'can_start' => false,
                'submitted_count' => $submittedCount,
                'opened_count' => $openedCount,
                'total_count' => $totalCount
            ];
        }

        return [
            'status' => 'completed',
            'message' => 'All bids have been opened',
            'can_start' => false,
            'submitted_count' => $submittedCount,
            'opened_count' => $openedCount,
            'total_count' => $totalCount
        ];
    }

    /**
     * Get status badge HTML for submission
     */
    private function getStatusBadge($submission): string
    {
        switch ($submission->BidStatus) {
            case 'submitted':
                return '<span class="badge bg-primary">📋 Submitted</span>';
            case 'responsive':
                return '<span class="badge bg-success">✅ Opened & Responsive</span>';
            case 'non-responsive':
                return '<span class="badge bg-danger">❌ Non-Responsive</span>';
            case 'evaluated':
                return '<span class="badge bg-info">📊 Evaluated</span>';
            case 'awarded':
                return '<span class="badge bg-warning">🏆 Awarded</span>';
            case 'rejected':
                return '<span class="badge bg-secondary">🚫 Rejected</span>';
            default:
                if ($submission->OpenedAt) {
                    return '<span class="badge bg-success">🔓 Opened</span>';
                }
                return '<span class="badge bg-warning">🔒 Sealed</span>';
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
