<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Procurement\BidSubmission;
use App\Models\Procurement\Tender;
use App\Services\Procurement\EncryptedBidDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BidOpeningCeremonyController extends Controller
{
    protected $documentService;

    public function __construct(EncryptedBidDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Display the bid opening ceremony interface
     */
    public function index(Request $request)
    {
        $this->authorize(\App\Enums\Core\PermissionEnum::BidOpeningRead->value);

        // Get tenders ready for opening (past submission deadline with submitted bids)
        $tenders = Tender::whereHas('submissions', function ($query) {
            $query->where('BidStatus', 'submitted');
        })
            ->where('SubmissionDeadline', '<', now())
            ->select('Id', 'TenderNo', 'Title', 'SubmissionDeadline', 'OpeningDate')
            ->orderBy('SubmissionDeadline', 'desc')
            ->get();

        $selectedTender = null;
        $submissions = collect();
        $ceremonyStatus = null;

        if ($request->has('tender')) {
            $selectedTender = Tender::where('TenderNo', $request->tender)->first();
            if ($selectedTender) {
                $submissions = BidSubmission::forTender($selectedTender->TenderNo)
                    ->with(['supplier.thirdParty', 'openedByUser'])
                    ->orderBy('ReceivedAt')
                    ->get();

                // Determine ceremony status
                $ceremonyStatus = $this->getCeremonyStatus($submissions);
            }
        }

        return view(
            'procurement.tendering.bidopeningandevaluation.opening.index',
            compact('tenders', 'selectedTender', 'submissions', 'ceremonyStatus')
        );
    }

    /**
     * Start the bid opening ceremony for a tender
     */
    public function startCeremony(Request $request)
    {
        $this->authorize(PermissionEnum::BidSubmissionWrite);

        $request->validate([
            'officers_present' => 'required|string',
            'ceremony_notes' => 'required|string|max:1000',
            'tender_ref' => 'required|exists:t_Tenders,TenderNo',
        ]);

        $tender = Tender::where('TenderNo', $request->tender_ref)->firstOrFail();

        // Validate that ceremony can be started
        if ($tender->SubmissionDeadline > now()) {
            return redirect()->back()->with('error', 'Cannot start ceremony before submission deadline.');
        }

        $submittedBids = BidSubmission::forTender($request->tender_ref)
            ->where('BidStatus', 'submitted')
            ->get();

        if ($submittedBids->isEmpty()) {
            return redirect()->back()->with('error', 'No submitted bids found for this tender.');
        }

        DB::beginTransaction();

        try {
            $openedCount = 0;

            // Open all submitted bids
            foreach ($submittedBids as $bid) {
                $bid->markAsOpened(Auth::user());
                $openedCount++;

                // Log individual bid opening
                activity()
                    ->performedOn($bid)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'action' => 'bid_opened',
                        'tender_ref' => $request->tender_ref,
                        'ceremony_notes' => $request->opening_notes,
                    ])
                    ->log("Bid opened during ceremony: {$bid->SupplierName}");
            }

            // Update tender opening status if needed
            if (! $tender->OpeningDate || $tender->OpeningDate > now()) {
                $tender->update(['OpeningDate' => now()]);
            }

            // Log ceremony start
            activity()
                ->performedOn($tender)
                ->causedBy(Auth::user())
                ->withProperties([
                    'action' => 'ceremony_started',
                    'bids_opened' => $openedCount,
                    'notes' => $request->opening_notes,
                ])
                ->log("Bid opening ceremony started for tender {$tender->TenderNo}");

            DB::commit();

            return redirect()->back()->with(
                'success',
                "Bid opening ceremony started successfully. {$openedCount} bids have been opened and are now accessible."
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to start bid opening ceremony: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to start ceremony: ' . $e->getMessage());
        }
    }

    /**
     * Access documents for a specific bid submission
     */
    public function accessDocuments($submissionId)
    {
        $this->authorize(PermissionEnum::BidSubmissionRead);

        $submission = BidSubmission::with(['supplier.thirdParty', 'tender'])->findOrFail($submissionId);

        // Check if documents can be accessed
        if (! $submission->canAccessDocuments()) {
            return redirect()->back()->with('error', 'Documents are sealed and cannot be accessed before bid opening ceremony.');
        }

        try {
            // Decrypt and prepare documents for viewing (support DMS-backed names)
            $encryptedDocs = json_decode($submission->EncryptedDocuments, true) ?? [];

            // Gather DMS document IDs and fetch metadata in bulk
            $docIds = collect($encryptedDocs)
                ->map(fn ($d) => $d['document_id'] ?? null)
                ->filter()
                ->values()
                ->all();

            $dmsDocs = [];
            if (! empty($docIds)) {
                $dmsDocs = \App\Models\DMS\Document::whereIn('DocumentId', $docIds)
                    ->with('current')
                    ->get()
                    ->keyBy('DocumentId');
            }

            $accessibleDocs = [];
            foreach ($encryptedDocs as $doc) {
                $documentId = $doc['document_id'] ?? null;
                $linked = $documentId && isset($dmsDocs[$documentId]) ? $dmsDocs[$documentId] : null;
                $name = $linked?->Name ?? ($doc['original_name'] ?? ($doc['original_filename'] ?? 'Unknown Document'));
                $sizeBytes = $linked?->current?->Size ?? ($doc['file_size'] ?? null);
                $uploadedAtVal = $linked?->getAttribute('CreatedOn');
                $uploadedAt = ($uploadedAtVal instanceof \Carbon\Carbon)
                    ? $uploadedAtVal->format('d/m/Y H:i:s')
                    : ($uploadedAtVal ?: ($doc['uploaded_at'] ?? null));

                $accessibleDocs[] = [
                    'id' => $documentId ?? ($doc['id'] ?? 'unknown'),
                    'name' => $name,
                    'size' => $sizeBytes !== null ? $this->formatFileSize($sizeBytes) : 'N/A',
                    'uploaded_at' => $uploadedAt,
                    'can_download' => true,
                ];
            }

            // Log document access
            activity()
                ->performedOn($submission)
                ->causedBy(Auth::user())
                ->withProperties([
                    'action' => 'documents_accessed',
                    'document_count' => count($accessibleDocs),
                ])
                ->log("Documents accessed for bid: {$submission->SupplierName}");

            return view(
                'procurement.tendering.bidopeningandevaluation.opening.documents',
                compact('submission', 'accessibleDocs')
            );
        } catch (\Exception $e) {
            Log::error('Failed to access bid documents: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to access documents: ' . $e->getMessage());
        }
    }

    /**
     * Download a specific document
     */
    public function downloadDocument($submissionId, $documentIndex)
    {
        $this->authorize(PermissionEnum::BidSubmissionRead);

        $submission = BidSubmission::findOrFail($submissionId);

        if (! $submission->canAccessDocuments()) {
            abort(403, 'Documents are sealed and cannot be accessed.');
        }

        try {
            // In a real implementation, this would decrypt and serve the actual file
            $encryptedDocs = json_decode($submission->EncryptedDocuments, true) ?? [];

            if (! isset($encryptedDocs[$documentIndex])) {
                abort(404, 'Document not found.');
            }

            $doc = $encryptedDocs[$documentIndex];

            // Log document download
            activity()
                ->performedOn($submission)
                ->causedBy(Auth::user())
                ->withProperties([
                    'action' => 'document_downloaded',
                    'document_name' => $doc['original_filename'] ?? 'unknown',
                    'document_id' => $doc['id'] ?? 'unknown',
                ])
                ->log("Document downloaded: {$doc['original_filename']} for {$submission->SupplierName}");

            // For demo purposes, return a response indicating download would start
            return response()->json([
                'message' => 'Document download would start here',
                'document' => $doc['original_filename'] ?? 'unknown',
                'supplier' => $submission->SupplierName,
                'note' => 'In production, this would decrypt and serve the actual file',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to download document: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to download document'], 500);
        }
    }

    /**
     * Generate bid opening summary report
     */
    public function generateSummary(Request $request, $tenderRef)
    {
        $this->authorize(PermissionEnum::BidSubmissionRead);

        $tender = Tender::where('TenderNo', $tenderRef)->firstOrFail();
        $submissions = BidSubmission::forTender($tenderRef)
            ->with(['supplier.thirdParty', 'openedByUser'])
            ->orderBy('BidAmount')
            ->get();

        $summary = [
            'tender_info' => [
                'tender_no' => $tender->TenderNo,
                'title' => $tender->Title,
                'opening_date' => $tender->OpeningDate,
                'submission_deadline' => $tender->SubmissionDeadline,
            ],
            'ceremony_details' => [
                'total_submissions' => $submissions->count(),
                'opened_submissions' => $submissions->where('OpenedAt', '!=', null)->count(),
                'ceremony_started' => $submissions->where('OpenedAt', '!=', null)->isNotEmpty(),
                'ceremony_date' => $submissions->where('OpenedAt', '!=', null)->first()?->OpenedAt,
            ],
            'bid_summary' => $submissions->map(function ($bid) {
                return [
                    'supplier_name' => $bid->SupplierName,
                    'bid_amount' => $bid->BidAmount,
                    'currency' => $bid->Currency,
                    'validity_period' => $bid->ValidityPeriod . ' days',
                    'delivery_period' => $bid->DeliveryPeriod . ' days',
                    'submission_date' => $bid->ReceivedAt,
                    'opened_at' => $bid->OpenedAt,
                    'opened_by' => $bid->openedByUser?->name,
                    'status' => $bid->BidStatus,
                    'documents_accessible' => $bid->DocumentsAccessible,
                ];
            }),
            'lowest_bid' => $submissions->where('BidAmount', '>', 0)->min('BidAmount'),
            'highest_bid' => $submissions->max('BidAmount'),
            'average_bid' => $submissions->where('BidAmount', '>', 0)->avg('BidAmount'),
            'generated_at' => now(),
            'generated_by' => Auth::user()->name,
        ];

        return response()->json($summary);
    }

    /**
     * Get ceremony status for a set of submissions
     */
    private function getCeremonyStatus($submissions)
    {
        $submittedCount = $submissions->where('BidStatus', 'submitted')->count();
        $openedCount = $submissions->whereNotNull('OpenedAt')->count();
        $totalCount = $submissions->count();

        if ($submittedCount === 0) {
            return [
                'status' => 'completed',
                'message' => 'All bids have been processed',
                'can_start' => false,
            ];
        }

        if ($openedCount === 0) {
            return [
                'status' => 'ready',
                'message' => "{$submittedCount} bids ready for opening",
                'can_start' => true,
            ];
        }

        if ($openedCount < $totalCount) {
            return [
                'status' => 'partial',
                'message' => "{$openedCount} of {$totalCount} bids opened",
                'can_start' => false,
            ];
        }

        return [
            'status' => 'completed',
            'message' => 'All bids have been opened',
            'can_start' => false,
        ];
    }

    /**
     * Format file size for display
     */
    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
