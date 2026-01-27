<?php

namespace App\Http\Controllers\API\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\BidSubmission;
use App\Models\ThirdParies\Supplier;
use App\Services\Procurement\EncryptedBidDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenderSubmissionApiController extends Controller
{
    public function index(): JsonResponse
    {
        $supplier = $this->resolveSupplier();

        if (!$supplier) {
            return response()->json(['error' => 'Supplier not found'], 404);
        }

        $submissions = BidSubmission::query()
            ->where('SupplierId', $supplier->Id)
            ->orderByDesc('CreatedOn')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->Id,
                'tenderRef' => $s->TenderRef,
                'submissionMode' => $s->submissionMode?->Description,
                'receivedAt' => $s->ReceivedAt,
                'status' => $s->DocumentsAccessible ? 'opened' : 'sealed',
                'createdOn' => $s->CreatedOn,
            ]);

        return response()->json([
            'data' => $submissions,
            'total' => $submissions->count(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tender_id' => 'required|integer|exists:t_Tenders,Id',
            'submission_mode' => 'required|string',
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

        if (!$submissionModeId) {
            return response()->json(['error' => 'Invalid submission mode'], 422);
        }

        $userId = DB::table('t_Users')->value('Id') ?? 1;

        DB::beginTransaction();

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
