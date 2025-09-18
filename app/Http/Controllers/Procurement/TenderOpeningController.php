<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\BidSubmission;
use App\Models\Procurement\Tender;
use App\Services\Procurement\EncryptedBidDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TenderOpeningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetching all tender openings can be done here if needed
        //return Tender::all();
        $tenders = Tender::select('Id', 'TenderNo', 'Title', 'Status')
            ->where('Status', '=', 'pb')
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $submissions = BidSubmission::where('TenderRef', $id)
            ->with(['createdByUser', 'modifiedByUser', 'submissionMode']) // Eager load users
            ->select('Id', 'SupplierName', 'SupplierId', 'SubmissionMode', 'ReceivedAt', 'CreatedBy', 'Remarks', 'ModifiedBy', 'SubmissionSource', 'DocumentsAccessible', 'BidOpeningDate', 'EncryptedDocuments')
            ->get()
            ->map(function ($submission) {
                $encryptedDocs = json_decode($submission->EncryptedDocuments, true) ?? [];
                $submission->document_count = count($encryptedDocs);
                $submission->status_badge = $this->getStatusBadge($submission);
                return $submission;
            });

        $tenders = Tender::select('Id', 'TenderNo', 'Title', 'Status')
            ->where('Status', '=', 'pb')
            ->get();
            
        $ceremonyStarted = EncryptedBidDocumentService::isCeremonyStarted($id);
        $data = true; // This variable is used to indicate that there are no submissions yet
        
        return view('procurement.tendering.bidopeningandevaluation.opening.index', compact('tenders', 'submissions', 'data', 'ceremonyStarted'));
    }

    /**
     * Start bid opening ceremony - unlock documents
     */
    public function startCeremony(Request $request)
    {
        $request->validate([
            'tender_ref' => 'required|string|exists:t_Tenders,TenderNo',
        ]);

        try {
            $unlockedCount = EncryptedBidDocumentService::startBidOpeningCeremony(
                $request->tender_ref,
                $request->user()
            );

            return redirect()->back()->with('success', 
                "Bid opening ceremony started successfully! {$unlockedCount} submissions unlocked.");

        } catch (\Exception $e) {
            Log::error('Error starting bid opening ceremony', [
                'tender_ref' => $request->tender_ref,
                'user_id' => $request->user()->Id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to start ceremony: ' . $e->getMessage());
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
     * Get status badge HTML for submission
     */
    private function getStatusBadge($submission): string
    {
        $status = $submission->getStatusAttribute();
        
        switch ($status) {
            case 'sealed':
                return '<span class="badge bg-warning">🔒 Sealed</span>';
            case 'opened':
                return '<span class="badge bg-success">🔓 Accessible</span>';
            case 'accessible':
                return '<span class="badge bg-info">📂 Ready</span>';
            default:
                return '<span class="badge bg-secondary">Unknown</span>';
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
