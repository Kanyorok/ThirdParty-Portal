<?php

namespace App\Http\Controllers\API\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQResponse;
use App\Models\Procurement\RFQClarification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplierRFQController extends Controller
{
    protected function thirdPartyUser()
    {
        return Auth::guard('sanctum')->user();
    }

    protected function supplierIdsForUser($user)
    {
        return DB::table('t_Suppliers as s')
            ->join('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
            ->where('sm.ThirdPartyId', $user->ThirdPartyId)
            ->where('s.Active_Status', 1)
            ->whereNull('s.DeletedOn')
            ->whereNull('sm.DeletedOn')
            ->pluck('s.Id');
    }

    public function listInvitations(Request $request): JsonResponse
    {
        try {
            $user = $this->thirdPartyUser();

            if (!$user || !isset($user->ThirdPartyId)) {
                return response()->json(['data' => []]);
            }

            $supplierIds = $this->supplierIdsForUser($user);

            if ($supplierIds->isEmpty()) {
                return response()->json(['data' => []]);
            }

            $data = DB::table('t_RFQ_Supplier as rs')
                ->join('t_RFQ as r', 'r.Id', '=', 'rs.RFQId')
                ->whereIn('rs.SupplierId', $supplierIds)
                ->whereNull('r.DeletedOn')
                ->select(
                    'r.Id as rfqId',
                    'r.RFQNumber as rfqNumber',
                    'r.Comments',
                    'r.Status',
                    'r.SubmissionDeadline',
                    'rs.Status as invitationStatus'
                )
                ->orderByDesc('r.Id')
                ->get();

            return response()->json(['data' => $data]);
        } catch (\Throwable $e) {
            Log::error('listInvitations failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function getInvitation(int|string $rfq): JsonResponse
    {
        try {
            $user = $this->thirdPartyUser();

            if (!$user || !isset($user->ThirdPartyId)) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            $supplierIds = $this->supplierIdsForUser($user);

            $invitation = DB::table('t_RFQ_Supplier as rs')
                ->join('t_RFQ as r', 'r.Id', '=', 'rs.RFQId')
                ->where('rs.RFQId', (int)$rfq)
                ->whereIn('rs.SupplierId', $supplierIds)
                ->whereNull('r.DeletedOn')
                ->select(
                    'r.Id as rfqId',
                    'r.RFQNumber as rfqNumber',
                    'r.Comments',
                    'r.Status',
                    'r.SubmissionDeadline',
                    'rs.SupplierId',
                    'rs.Status as invitationStatus',
                    'rs.CreatedOn',
                    'rs.ModifiedOn'
                )
                ->first();

            if (!$invitation) {
                return response()->json(['error' => 'Not Found'], 404);
            }

            return response()->json(['data' => $invitation]);
        } catch (\Throwable $e) {
            Log::error('getInvitation failed', ['rfq' => $rfq, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function submitResponse(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'rfqId' => 'required|integer|exists:t_RFQ,Id',
                'currency' => 'required|string|max:3',
                'durationDays' => 'required|integer|min:1',
                'items' => 'required|array|min:1',
                'items.*.rfqLineId' => 'required|integer|exists:t_RFQLines,Id',
                'items.*.quotedPrice' => 'required|numeric|min:0',
                'items.*.totalPayable' => 'required|numeric|min:0',
                'isDraft' => 'sometimes|boolean'
            ]);

            $user = $this->thirdPartyUser();

            if (!$user || !isset($user->ThirdPartyId)) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            $supplierIds = $this->supplierIdsForUser($user);

            $supplierId = DB::table('t_RFQ_Supplier')
                ->where('RFQId', $validated['rfqId'])
                ->whereIn('SupplierId', $supplierIds)
                ->value('SupplierId');

            if (!$supplierId) {
                return response()->json(['error' => 'No invitation found'], 403);
            }

            $existing = RFQResponse::where('RFQId', $validated['rfqId'])
                ->where('SupplierId', $supplierId)
                ->whereNull('DeletedOn')
                ->first();

            if ($existing && strtoupper($existing->Status) === 'FINAL') {
                return response()->json(['error' => 'Already submitted'], 409);
            }

            DB::beginTransaction();

            $response = $existing ?? RFQResponse::create([
                'RFQId' => $validated['rfqId'],
                'SupplierId' => $supplierId,
                'SubmittedBy' => $user->Id,
                'Currency' => $validated['currency'],
                'DurationDays' => $validated['durationDays'],
                'Status' => ($validated['isDraft'] ?? false) ? 'DRAFT' : 'FINAL',
                'SubmittedOn' => now()
            ]);

            if ($existing) {
                $response->update([
                    'Currency' => $validated['currency'],
                    'DurationDays' => $validated['durationDays'],
                    'Status' => ($validated['isDraft'] ?? false) ? 'DRAFT' : 'FINAL',
                    'SubmittedOn' => now()
                ]);

                $response->items()->delete();
            }

            foreach ($validated['items'] as $item) {
                $response->items()->create([
                    'RFQLineId' => $item['rfqLineId'],
                    'QuotedPrice' => $item['quotedPrice'],
                    'TotalPayable' => $item['totalPayable']
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Response submitted successfully']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('submitResponse failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function postClarification(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'rfqId' => 'required|integer|exists:t_RFQ,Id',
                'question' => 'required|string|max:1000',
                'rfqLineId' => 'nullable|integer|exists:t_RFQLines,Id'
            ]);

            $user = $this->thirdPartyUser();

            if (!$user || !isset($user->ThirdPartyId)) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            $supplierId = DB::table('t_Suppliers as s')
                ->join('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
                ->where('sm.ThirdPartyId', $user->ThirdPartyId)
                ->where('s.Active_Status', 1)
                ->whereNull('s.DeletedOn')
                ->whereNull('sm.DeletedOn')
                ->value('s.Id');

            if (!$supplierId) {
                return response()->json(['error' => 'No invitation found'], 403);
            }

            RFQClarification::create([
                'RFQId' => $validated['rfqId'],
                'SupplierId' => $supplierId,
                'RFQLineId' => $validated['rfqLineId'],
                'Question' => $validated['question'],
                'CreatedBy' => $user->Id,
                'ModifiedBy' => $user->Id
            ]);

            return response()->json(['message' => 'Clarification submitted'], 201);
        } catch (\Throwable $e) {
            Log::error('postClarification failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function listClarifications(int|string $rfq): JsonResponse
    {
        try {
            $user = $this->thirdPartyUser();

            if (!$user || !isset($user->ThirdPartyId)) {
                return response()->json(['data' => []]);
            }

            $supplierIds = $this->supplierIdsForUser($user);

            $data = RFQClarification::where('RFQId', (int)$rfq)
                ->whereIn('SupplierId', $supplierIds)
                ->whereNull('DeletedOn')
                ->orderByDesc('Id')
                ->get([
                    'Id',
                    'RFQId',
                    'SupplierId',
                    'RFQLineId',
                    'Question',
                    'Answer',
                    'CreatedOn'
                ]);

            return response()->json(['data' => $data]);
        } catch (\Throwable $e) {
            Log::error('listClarifications failed', ['rfq' => $rfq, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
