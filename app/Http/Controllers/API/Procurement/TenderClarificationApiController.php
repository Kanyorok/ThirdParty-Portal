<?php

namespace App\Http\Controllers\API\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\VendorClarifications;
use App\Models\ThirdParies\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenderClarificationApiController extends Controller
{
    public function submitClarification(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tender_id' => 'required|integer|exists:t_Tenders,Id',
            'question' => 'required|string|min:5|max:2000',
            'is_public' => 'sometimes|boolean',
        ]);

        $supplier = $this->resolveSupplier();

        if (!$supplier) {
            return response()->json(['error' => 'Supplier not found'], 404);
        }

        $hasAccess = DB::table('t_TenderInvitations')
            ->where('TenderId', $data['tender_id'])
            ->where('SupplierId', $supplier->Id)
            ->whereNull('DeletedOn')
            ->whereRaw('LOWER(ResponseStatus) = ?', ['accepted'])
            ->exists();

        if (!$hasAccess) {
            $hasAccess = DB::table('t_TenderSuppliers')
                ->where('TenderID', $data['tender_id'])
                ->where('SupplierID', $supplier->Id)
                ->whereNull('DeletedOn')
                ->exists();
        }

        if (!$hasAccess) {
            return response()->json(['error' => 'Invitation not accepted'], 403);
        }

        $userId = DB::table('t_Users')->value('Id') ?? 1; // TODO: Get actual user ID

        $clarificationId = DB::table('t_VendorClarifications')->insertGetId([
            'TenderID' => (int) $data['tender_id'],
            'VendorID' => (int) $supplier->Id,
            'Question' => $data['question'],
            'QuestionDate' => now(),
            'ISPUBLISHEDTOALL' => (bool) ($data['is_public'] ?? false),
            'CreatedBy' => $userId,
            'CreatedOn' => now(),
            'ModifiedBy' => $userId,
            'ModifiedOn' => now(),
        ], 'ClarificationID');

        return response()->json([
            'message' => 'Clarification submitted successfully',
            'data' => [
                'clarificationId' => $clarificationId,
                'tenderId' => $data['tender_id'],
                'question' => $data['question'],
                'status' => 'pending',
            ],
        ], 201);
    }

    public function getClarifications(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tender_id' => 'required|integer|exists:t_Tenders,Id',
        ]);

        $supplier = $this->resolveSupplier();

        if (!$supplier) {
            return response()->json(['error' => 'Supplier not found'], 404);
        }

        $hasAccess = DB::table('t_TenderInvitations')
            ->where('TenderId', $data['tender_id'])
            ->where('SupplierId', $supplier->Id)
            ->whereNull('DeletedOn')
            ->whereRaw('LOWER(ResponseStatus) = ?', ['accepted'])
            ->exists();

        if (!$hasAccess) {
            $hasAccess = DB::table('t_TenderSuppliers')
                ->where('TenderID', $data['tender_id'])
                ->where('SupplierID', $supplier->Id)
                ->whereNull('DeletedOn')
                ->exists();
        }

        if (!$hasAccess) {
            return response()->json(['error' => 'Invitation not accepted'], 403);
        }

        $clarifications = VendorClarifications::query()
            ->where('TenderID', $data['tender_id'])
            ->whereNull('DeletedOn')
            ->where(function ($q) use ($supplier) {
                $q->where('VendorID', $supplier->Id)
                  ->orWhere('ISPUBLISHEDTOALL', true);
            })
            ->orderByDesc('QuestionDate')
            ->get()
            ->map(fn ($c) => [
                'clarificationId' => $c->ClarificationID,
                'question' => $c->Question,
                'questionDate' => $c->QuestionDate,
                'answer' => $c->Answer,
                'answerDate' => $c->AnswerDate,
                'isPublic' => (bool) $c->ISPUBLISHEDTOALL,
                'isOwnQuestion' => $c->VendorID === $supplier->Id,
                'status' => $c->Answer ? 'answered' : 'pending',
            ]);

        return response()->json([
            'data' => $clarifications,
            'total' => $clarifications->count(),
            'tenderId' => $data['tender_id'],
        ]);
    }

    private function resolveSupplier(): ?Supplier
    {
        $user = Auth::guard('third_party')->user() ?? Auth::user();

        if (!$user) {
            return null;
        }

        $thirdPartyId = $user->ThirdPartyId
            ?? DB::table('t_ThirdPartyUsers')->where('Id', $user->Id)->value('ThirdPartyId');

        if (!$thirdPartyId) {
            return null;
        }

        return Supplier::whereHas('supplierMaster', fn ($q) =>
            $q->where('ThirdPartyId', $thirdPartyId)
        )->first();
    }
}
