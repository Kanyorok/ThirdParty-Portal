<?php

namespace App\Http\Controllers\API\Procurement;

use App\Http\Controllers\Controller;
use App\Services\Procurement\SupplierRFQService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SupplierRFQController extends Controller
{
    protected SupplierRFQService $rfqService;

    public function __construct(SupplierRFQService $rfqService)
    {
        $this->rfqService = $rfqService;
    }

    /**
     * Get all RFQ invitations for authenticated supplier
     */
    public function listInvitations(Request $request): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        $thirdPartyId = ($user instanceof \App\Models\ThirdParty\ThirdPartyUser) ? $user->ThirdPartyId : null;
        
        if (!$thirdPartyId) {
            return response()->json(['data' => []]);
        }

        // FIXED: Get supplier IDs through SupplierMaster
        $mySupplierIds = DB::table('t_Suppliers as s')
            ->join('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
            ->where('sm.ThirdPartyId', $thirdPartyId)
            ->where('s.Active_Status', 1)
            ->whereNull('s.DeletedOn')
            ->whereNull('sm.DeletedOn')
            ->pluck('s.Id');

        $invitations = DB::table('t_RFQ_Supplier as p')
            ->join('t_RFQ as r', 'r.Id', '=', 'p.RFQId')
            ->whereIn('p.SupplierId', $mySupplierIds)
            ->select(
                'r.Id as rfqId',
                'r.RFQNumber as number',
                'r.Comments as comments',
                'r.Status as status',
                'r.SubmissionDeadline as submissionDeadline',
                'p.Status as invitationStatus'
            )
            ->orderByDesc('r.Id')
            ->get();

            return response()->json([
                'error' => 'Internal Server Error',
                'message' => 'An error occurred while fetching RFQ invitations'
            ], 500);
        }
    }

    /**
     * Get detailed RFQ invitation
     */
    public function getInvitation(int|string $rfq): JsonResponse
    {
        $rfqId = (int) $rfq;
        $user = Auth::guard('sanctum')->user();
        $thirdPartyId = ($user instanceof \App\Models\ThirdParty\ThirdPartyUser) ? $user->ThirdPartyId : null;
        
        if (!$thirdPartyId) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $rfq = RFQ::find($rfqId);
        if (!$rfq) {
            return response()->json(['error' => 'RFQ not found'], 404);
        }

        // FIXED: Get supplier IDs through SupplierMaster
        $mySupplierIds = DB::table('t_Suppliers as s')
            ->join('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
            ->where('sm.ThirdPartyId', $thirdPartyId)
            ->where('s.Active_Status', 1)
            ->whereNull('s.DeletedOn')
            ->whereNull('sm.DeletedOn')
            ->pluck('s.Id');

        $invitation = DB::table('t_RFQ_Supplier')
            ->where('RFQId', $rfqId)
            ->whereIn('SupplierId', $mySupplierIds)
            ->select('SupplierId', 'Status', 'CreatedOn', 'ModifiedOn')
            ->first();

            if (!$invitation) {
                return response()->json([
                    'error' => 'Not Found',
                    'message' => 'RFQ not found or you do not have access to it'
                ], 404);
            }

            return response()->json([
                'success' => true,
                ...(array)$invitation
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching RFQ invitation details', [
                'rfq_id' => $rfq,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Internal Server Error',
                'message' => 'An error occurred while fetching RFQ details'
            ], 500);
        }
    }

    /**
     * Submit RFQ response
     */
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
                'isDraft' => 'sometimes|boolean',
            ]);

        $user = Auth::guard("sanctum")->user();
        $thirdPartyId = ($user instanceof \App\Models\ThirdParty\ThirdPartyUser) ? $user->ThirdPartyId : null;
        if (!$thirdPartyId) {
            return response()->json(["error" => "Authentication required"], 401);
        }

        $rfq = RFQ::find($request->rfqId);
        if (!$rfq) {
            return response()->json(['error' => 'RFQ not found'], 404);
        }

        // FIXED: Get supplier IDs through SupplierMaster
        $mySupplierIds = DB::table('t_Suppliers as s')
            ->join('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
            ->where('sm.ThirdPartyId', $thirdPartyId)
            ->where('s.Active_Status', 1)
            ->whereNull('s.DeletedOn')
            ->whereNull('sm.DeletedOn')
            ->pluck('s.Id');

        $supplierId = DB::table('t_RFQ_Supplier')
            ->where('RFQId', $rfq->Id)
            ->whereIn('SupplierId', $mySupplierIds)
            ->min('SupplierId');

        if (!$supplierId) {
            return response()->json(['error' => 'No invitation found for this supplier'], 403);
        }

        // FIXED: Get trading name through SupplierMaster
        $tradingName = DB::table('t_Suppliers as s')
            ->join('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
            ->join('t_ThirdParties as tp', 'tp.Id', '=', 'sm.ThirdPartyId')
            ->where('s.Id', $supplierId)
            ->value('tp.TradingName');

        $actor = SystemHelper::user();

        $existing = RFQResponse::where('RFQId', $rfq->Id)
            ->where('SupplierId', $supplierId)
            ->whereNull('DeletedOn')
            ->first();

        if ($existing && strtoupper($existing->Status ?? 'FINAL') === 'FINAL') {
            return response()->json([
                'error' => $statusCode === 409 ? 'Already Submitted' : 'Internal Server Error',
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    /**
     * Submit clarification request
     */
    public function postClarification(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'rfqId' => 'required|integer|exists:t_RFQ,Id',
                'question' => 'required|string|max:1000',
                'rfqLineId' => 'nullable|integer|exists:t_RFQLines,Id',
            ]);

        $user = Auth::guard("sanctum")->user();
        $thirdPartyId = ($user instanceof \App\Models\ThirdParty\ThirdPartyUser) ? $user->ThirdPartyId : null;
        if (!$thirdPartyId) {
            return response()->json(["error" => "Authentication required"], 401);
        }

        // FIXED: Get supplier IDs through SupplierMaster
        $mySupplierIds = DB::table('t_Suppliers as s')
            ->join('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
            ->where('sm.ThirdPartyId', $thirdPartyId)
            ->where('s.Active_Status', 1)
            ->whereNull('s.DeletedOn')
            ->whereNull('sm.DeletedOn')
            ->pluck('s.Id');

        $supplierId = DB::table('t_RFQ_Supplier')
            ->where('RFQId', $request->rfqId)
            ->whereIn('SupplierId', $mySupplierIds)
            ->min('SupplierId');

        if (!$supplierId) {
            return response()->json(['error' => 'No invitation found for this supplier'], 403);
        }

        $actor = SystemHelper::user();
        RFQClarification::create([
            'RFQId' => $request->rfqId,
            'SupplierId' => $supplierId,
            'RFQLineId' => $request->rfqLineId,
            'Question' => $request->question,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        return response()->json(['message' => 'Clarification submitted'], 201);
    }

    public function listClarifications(int|string $rfq): JsonResponse
    {
        $rfqId = (int) $rfq;
        $user = Auth::guard('sanctum')->user();
        $thirdPartyId = ($user instanceof \App\Models\ThirdParty\ThirdPartyUser) ? $user->ThirdPartyId : null;
        
        if (!$thirdPartyId) {
            return response()->json(['data' => []]);
        }

        // FIXED: Get supplier IDs through SupplierMaster
        $mySupplierIds = DB::table('t_Suppliers as s')
            ->join('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
            ->where('sm.ThirdPartyId', $thirdPartyId)
            ->whereNull('s.DeletedOn')
            ->whereNull('sm.DeletedOn')
            ->pluck('s.Id');

        $clarifications = RFQClarification::query()
            ->where('RFQId', $rfqId)
            ->whereIn('SupplierId', $mySupplierIds)
            ->whereNull('DeletedOn')
            ->orderByDesc('Id')
            ->get(['Id', 'RFQId', 'SupplierId', 'RFQLineId', 'Question', 'Answer', 'CreatedOn']);

        return response()->json(['data' => $clarifications]);
    }
}

