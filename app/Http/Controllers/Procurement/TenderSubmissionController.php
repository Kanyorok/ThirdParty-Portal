<?php

namespace App\Http\Controllers\API\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\BidSubmission;
use App\Models\Procurement\Tender;
use App\Models\ThirdParies\Supplier;
use App\Services\Procurement\EncryptedBidDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenderSubmissionController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize(\App\Enums\Core\PermissionEnum::BidSubmissionRead->value);
        $submissions = BidSubmission::with([
            'submissionMode',
            'createdByUser',
            'supplier.supplierMaster.party',
        ])
            ->orderBy('CreatedOn', 'desc')
            ->get();

        return view('procurement.tendering.suppliermanagement.bidsubmission.index', compact('submissions'));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize(\App\Enums\Core\PermissionEnum::BidSubmissionWrite->value);
        // Exclude tenders that already have submissions
        $tenders = Tender::select('TenderNo', 'Title')
            ->doesntHave('submissions')
            ->get();

        // Fix: Get supplier names from the related ThirdParty table
        $suppliers = Supplier::select('t_Suppliers.Id')
            ->join('t_SupplierMaster', 't_Suppliers.SupplierMasterId', '=', 't_SupplierMaster.Id')
            ->join('t_ThirdParties', 't_SupplierMaster.ThirdPartyId', '=', 't_ThirdParties.Id')
            ->selectRaw('t_Suppliers.Id, COALESCE(t_ThirdParties.TradingName, t_ThirdParties.ThirdPartyName) as SupplierName')
            ->whereNull('t_Suppliers.DeletedOn')
            ->get();

        $submissionModes = DB::table('t_CodeDetails')
            ->where('CodeID', 'SubmissionMode')
            ->get(['ID', 'Description']);

        return view('procurement.tendering.suppliermanagement.bidsubmission.create', compact('tenders', 'suppliers', 'submissionModes'));
    }

    public function view($Id)
    {
        $submission = BidSubmission::findOrFail($Id);

        return view('procurement.tendering.suppliermanagement.bidsubmission.view', compact('submission'));
    }

    public function edit($Id)
    {
        $submission = BidSubmission::findOrFail($Id);
    }

    public function store(Request $request)
    {
        // Validate the input
        $request->validate([
            'tender_ref' => 'required|string|max:255',
            'supplier_name' => 'required|string|max:255',
            'submission_mode' => 'required|string|max:255',
            'received_at' => 'required|date',
            'recorded_by' => 'required|string|max:255',
            'remarks' => 'nullable|string',
            'bid_file' => 'required|file|mimes:zip,pdf|max:10240',
        ]);

        $supplier = $this->resolveSupplier();

        if (!$supplier) {
            return response()->json(['error' => 'Supplier not found'], 404);
        }

        $invited = DB::table('t_TenderInvitations')
            ->where('TenderId', $data['tender_id'])
            ->where('SupplierId', $supplier->Id)
            ->where('ResponseStatus', 'accepted')
            ->whereNull('DeletedOn')
            ->exists();

        if (!$invited) {
            return response()->json(['error' => 'Tender invitation not accepted'], 403);
        }

        $exists = BidSubmission::query()
            ->where('TenderRef', $data['tender_id'])
            ->where('SupplierId', $supplier->Id)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Bid already submitted'], 409);
        }

        $submissionModeId = DB::table('t_CodeDetails')
            ->where('CodeID', 'SubmissionMode')
            ->where('Description', $data['submission_mode'])
            ->value('ID');

        if (! $submissionModeId) {
            return redirect()->back()->withErrors(['submission_mode' => 'Invalid submission mode selected.']);
        }

        $userId = DB::table('t_Users')->value('Id') ?? 1;

        DB::beginTransaction();

        try {
            // Create bid submission record
            $bidSubmission = BidSubmission::create([
                'TenderRef' => $request->tender_ref,
                'SupplierName' => $request->supplier_name,
                'SupplierId' => $supplier?->Id,
                'SubmissionMode' => $submissionModeId,
                'ReceivedAt' => $request->received_at,
                'RecordedBy' => $request->recorded_by,
                'Remarks' => $request->remarks,
                'SubmissionSource' => 'manual',
                'DocumentsAccessible' => false, // Sealed until bid opening
                'CreatedBy' => $request->user()->Id,
                'ModifiedBy' => $request->user()->Id,
            ]);

        $submission = BidSubmission::create([
            'TenderRef' => $data['tender_id'],
            'SupplierId' => $supplier->Id,
            'SupplierName' => $supplier->supplierMaster?->thirdParty?->TradingName,
            'SubmissionMode' => $submissionModeId,
            'ReceivedAt' => now(),
            'RecordedBy' => 'portal',
            'Remarks' => $data['remarks'] ?? null,
            'SubmissionSource' => 'portal',
            'DocumentsAccessible' => false,
            'CreatedBy' => $userId,
            'ModifiedBy' => $userId,
        ]);

        $encrypted = EncryptedBidDocumentService::storeEncryptedBidDocuments(
            $submission,
            [$request->file('bid_file')],
            Auth::user()
        );

        $submission->update([
            'EncryptedDocuments' => json_encode($encrypted),
            'ModifiedBy' => $userId,
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Bid submitted successfully',
            'data' => [
                'submissionId' => $submission->Id,
                'tenderId' => $data['tender_id'],
                'status' => 'sealed',
            ],
        ], 201);
    }

    private function resolveSupplier(): ?Supplier
    {
        $user = Auth::user();

        if (property_exists($user, 'ThirdPartyId') && $user->ThirdPartyId) {
            return Supplier::whereHas('supplierMaster', fn ($q) =>
                $q->where('ThirdPartyId', $user->ThirdPartyId)
            )->first();
        }

        $tpu = DB::table('t_ThirdPartyUsers')->where('Id', $user->Id)->first();

        if ($tpu?->ThirdPartyId) {
            return Supplier::whereHas('supplierMaster', fn ($q) =>
                $q->where('ThirdPartyId', $tpu->ThirdPartyId)
            )->first();
        }

        return null;
    }
}
