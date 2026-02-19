<?php

namespace App\Http\Controllers\API\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\TenderInvitations\ListTenderInvitationsRequest;
use App\Http\Requests\Procurement\TenderInvitations\RespondTenderInvitationRequest;
use App\Http\Resources\Procurement\TenderInvitationResource;
use App\Http\Resources\Procurement\TenderInvitationResponseResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenderInvitationResponseApiController extends Controller
{
    public function index(ListTenderInvitationsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = Auth::guard('third_party')->user()
            ?? Auth::guard('sanctum')->user()
            ?? Auth::user();

        $thirdPartyId = $this->resolveThirdPartyId($request, $user);
        $supplierIds = [];

        if (empty($validated['supplier_id']) && ! $thirdPartyId) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to determine third party context',
                'error' => 'Unable to determine third party context',
            ], 400);
        }

        if (! empty($validated['supplier_id'])) {
            $supplierIds = [(int) $validated['supplier_id']];
        } elseif ($thirdPartyId) {
            $supplierIds = $this->resolveSupplierIds($thirdPartyId);
        }

        if (empty($supplierIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No supplier found for this account',
                'error' => 'No supplier found for this account',
            ], 404);
        }

        $query = DB::table('t_TenderInvitations as ti')
            ->join('t_Tenders as t', 'ti.TenderId', '=', 't.Id')
            ->leftJoin('t_Currencies as c', 't.CurrencyId', '=', 'c.Id')
            ->whereNull('ti.DeletedOn')
            ->whereIn('ti.SupplierId', $supplierIds);

        if (! empty($validated['tender_id'])) {
            $query->where('ti.TenderId', $validated['tender_id']);
        }

        if (! empty($validated['status'])) {
            $query->where('ti.ResponseStatus', $this->normalizeStatus($validated['status']));
        }

        $invitations = $query
            ->orderByDesc('ti.InvitationDate')
            ->select([
                'ti.InvitationID',
                'ti.TenderId',
                'ti.SupplierId',
                'ti.ResponseStatus',
                'ti.ResponseDate',
                'ti.DeclineReason',
                'ti.InvitationDate',
                't.TenderNo',
                't.Title',
                't.TenderType',
                't.TenderCategory',
                't.SubmissionDeadline',
                't.OpeningDate',
                't.Status as TenderStatus',
                't.CurrencyId',
                'c.Code as CurrencyCode',
            ])
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Tender invitations retrieved successfully.',
            'data' => TenderInvitationResource::collection($invitations)->toArray($request),
            'total' => $invitations->count(),
        ]);
    }

    public function respond(RespondTenderInvitationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = Auth::guard('third_party')->user()
            ?? Auth::guard('sanctum')->user()
            ?? Auth::user();

        $thirdPartyId = $this->resolveThirdPartyId($request, $user);
        if (! $thirdPartyId) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to determine third party context',
                'error' => 'Unable to determine third party context',
            ], 400);
        }

        $supplierIds = $this->resolveSupplierIds($thirdPartyId);
        if (empty($supplierIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No supplier found for this account',
                'error' => 'No supplier found for this account',
            ], 404);
        }

        $invitation = DB::table('t_TenderInvitations')
            ->where('TenderId', $validated['tender_id'])
            ->whereIn('SupplierId', $supplierIds)
            ->whereNull('DeletedOn')
            ->orderByDesc('InvitationDate')
            ->first();

        if (! $invitation) {
            return response()->json([
                'success' => false,
                'message' => 'Invitation not found',
                'error' => 'Invitation not found',
            ], 404);
        }

        $status = $this->normalizeStatus($validated['response_status']);
        $userId = $this->resolveSystemUserId($user);

        $update = [
            'ResponseStatus' => $status,
            'ResponseDate' => now(),
            'ModifiedBy' => $userId,
            'ModifiedOn' => now(),
        ];

        if ($status === 'Declined') {
            $update['DeclineReason'] = $validated['decline_reason'] ?? null;
        }

        DB::table('t_TenderInvitations')
            ->where('InvitationID', $invitation->InvitationID)
            ->update($update);

        $updated = DB::table('t_TenderInvitations')
            ->where('InvitationID', $invitation->InvitationID)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Invitation response updated successfully',
            'data' => (new TenderInvitationResponseResource($updated))->toArray($request),
        ]);
    }

    private function resolveThirdPartyId(Request $request, $user = null): ?int
    {
        if ($request->filled('third_party_id')) {
            return (int)$request->input('third_party_id');
        }

        if ($user instanceof \App\Models\ThirdParty\ThirdPartyUser) {
            return (int)$user->ThirdPartyId;
        }

        if ($user && property_exists($user, 'ThirdPartyId') && $user->ThirdPartyId) {
            return (int)$user->ThirdPartyId;
        }

        return null;
    }

    private function resolveSupplierIds(int $thirdPartyId): array
    {
        return DB::table('t_Suppliers')
            ->join('t_SupplierMaster', 't_Suppliers.SupplierMasterId', '=', 't_SupplierMaster.Id')
            ->where('t_SupplierMaster.ThirdPartyId', $thirdPartyId)
            ->whereNull('t_Suppliers.DeletedOn')
            ->pluck('t_Suppliers.Id')
            ->values()
            ->all();
    }

    private function normalizeStatus(string $value): string
    {
        return match (strtolower(trim($value))) {
            'accepted' => 'Accepted',
            'declined' => 'Declined',
            default => 'Pending',
        };
    }

    private function resolveSystemUserId($user): int
    {
        $userId = $user?->getAuthIdentifier();
        if ($userId && DB::table('t_Users')->where('Id', $userId)->exists()) {
            return (int)$userId;
        }

        // Fallback to system user to satisfy FK constraints
        return 1;
    }
}
