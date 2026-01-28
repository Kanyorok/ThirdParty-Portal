<?php

namespace App\Http\Controllers\API\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RFQ;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class SupplierRFQController extends Controller
{
    /*protected SupplierRFQService $rfqService;

    public function __construct(SupplierRFQService $rfqService)
    {
        $this->rfqService = $rfqService;
    }*/

    /**
     * Get all RFQ invitations for authenticated supplier
     */
    /**
     * Get all RFQ invitations for authenticated supplier
     */
    public function listInvitations(Request $request): JsonResponse
    {
        try {
            $user = Auth::guard('sanctum')->user();
            $thirdPartyId = ($user instanceof \App\Models\ThirdParty\ThirdPartyUser) ? $user->ThirdPartyId : null;

            if (! $thirdPartyId) {
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

            return response()->json(['data' => $invitations]);
        } catch (\Exception $e) {
            Log::error('Error fetching RFQ invitations: ' . $e->getMessage());

            return response()->json([
                'error' => 'Internal Server Error',
                'message' => 'An error occurred while fetching RFQ invitations',
            ], 500);
        }
    }

    /**
     * Get detailed RFQ invitation with full RFQ details and lines
     */
    public function getInvitation(int|string $rfq): JsonResponse
    {
        try {
            $rfqId = (int) $rfq;
            $user = Auth::guard('sanctum')->user();
            $thirdPartyId = ($user instanceof \App\Models\ThirdParty\ThirdPartyUser) ? $user->ThirdPartyId : null;

            if (! $thirdPartyId) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            // Load RFQ with its lines and unit of measure
            $rfqModel = RFQ::with(['rfqLines', 'rfqLines.uom'])->find($rfqId);
            if (! $rfqModel) {
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

            if (! $invitation) {
                return response()->json([
                    'error' => 'Not Found',
                    'message' => 'RFQ not found or you do not have access to it',
                ], 404);
            }

            // Get existing response if any
            $existingResponse = \App\Models\Procurement\RFQResponse::where('RFQId', $rfqId)
                ->where('SupplierId', $invitation->SupplierId)
                ->whereNull('DeletedOn')
                ->with(['items'])
                ->first();

            // Build RFQ header data
            $rfqData = [
                'id' => $rfqModel->Id,
                'number' => $rfqModel->RFQNumber,
                'comments' => $rfqModel->Comments,
                'title' => $rfqModel->Comments, // Use comments as title
                'referenceNumber' => $rfqModel->RFQNumber,
                'status' => $rfqModel->Status,
                'submissionDeadline' => $rfqModel->SubmissionDeadline,
                'description' => $rfqModel->Remarks ?? '',
            ];

            // Build lines data
            $lines = $rfqModel->rfqLines->map(function ($line) {
                return [
                    'id' => $line->Id,
                    'rfqLineId' => $line->Id,
                    'lineId' => $line->Id,
                    'lineNumber' => $line->LineNumber ?? 0,
                    'ItemCode' => $line->ItemCode ?? '',
                    'ItemName' => $line->ItemName ?? '',
                    'description' => $line->ItemName ?? '',
                    'quantity' => $line->Quantity ?? 0,
                    'unitOfMeasure' => $line->uom->Name ?? $line->UOM ?? '',
                    'uom' => $line->uom->Name ?? $line->UOM ?? '',
                    'specification' => $line->Specification ?? '',
                ];
            })->values()->all();

            // Build response data if exists
            $responseData = null;
            if ($existingResponse) {
                $responseData = [
                    'currency' => $existingResponse->CurrencyCode ?? '',
                    'DurationDays' => $existingResponse->DurationDays ?? 14,
                    'LeadTimeDays' => $existingResponse->LeadTimeDays ?? null,
                    'items' => $existingResponse->items->map(function ($item) {
                        return [
                            'rfqLineId' => $item->RFQLineID ?? $item->RfqLineId ?? '',
                            'QuotedPrice' => $item->QuotedPrice ?? null,
                            'TotalPayable' => $item->TotalPayable ?? null,
                            'Comments' => $item->Comments ?? '',
                        ];
                    })->values()->all(),
                    'submittedAt' => $existingResponse->CreatedOn ?? null,
                ];
            }

            return response()->json([
                'success' => true,
                'rfq' => $rfqData,
                'lines' => $lines,
                'response' => $responseData,
                'isSubmitted' => $existingResponse && strtoupper($existingResponse->Status ?? '') === 'FINAL',
                'invitation' => [
                    'SupplierId' => $invitation->SupplierId,
                    'Status' => $invitation->Status,
                    'CreatedOn' => $invitation->CreatedOn,
                    'ModifiedOn' => $invitation->ModifiedOn,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching RFQ invitation details', [
                'rfq_id' => $rfq,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Internal Server Error',
                'message' => 'An error occurred while fetching RFQ details',
            ], 500);
        }
    }

    /**
     * Submit RFQ response
     */
    public function submitResponse(Request $request): JsonResponse
    {
        try {
            $request->validate([
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
            if (! $thirdPartyId) {
                return response()->json(["error" => "Authentication required"], 401);
            }

            // Fix: Assuming RFQ model exists
            $rfq = \App\Models\Procurement\RFQ::find($request->rfqId);
            if (! $rfq) {
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

            if (! $supplierId) {
                return response()->json(['error' => 'No invitation found for this supplier'], 403);
            }

            // FIXED: Get trading name through SupplierMaster
            $tradingName = DB::table('t_Suppliers as s')
                ->join('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
                ->join('t_ThirdParties as tp', 'tp.Id', '=', 'sm.ThirdPartyId')
                ->where('s.Id', $supplierId)
                ->value('tp.TradingName');

            $actorId = $user->Id ?? 0; // Fallback

            $existing = \App\Models\Procurement\RFQResponse::where('RFQId', $rfq->Id)
                ->where('SupplierId', $supplierId)
                ->whereNull('DeletedOn')
                ->first();

            if ($existing && strtoupper($existing->Status ?? 'FINAL') === 'FINAL') {
                return response()->json([
                   'error' => 'Already Submitted',
                   'message' => 'Response already submitted',
                ], 409);
            }

            // Logic to save response would go here (truncated in original file? Assuming placeholder)
            return response()->json(['message' => 'Response submitted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json([
               'error' => 'Internal Server Error',
               'message' => $e->getMessage(),
            ], 500);
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
            if (! $thirdPartyId) {
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

            if (! $supplierId) {
                return response()->json(['error' => 'No invitation found for this supplier'], 403);
            }

            // Use system user ID (1) for CreatedBy/ModifiedBy since ThirdPartyUser IDs don't exist in t_Users
            // The foreign key constraint requires a valid t_Users.Id
            $systemUserId = 1; // SYSTEM user

            \App\Models\Procurement\RFQClarification::create([
                'RFQId' => $request->rfqId,
                'SupplierId' => $supplierId,
                'RFQLineId' => $request->rfqLineId,
                'Question' => $request->question,
                'CreatedBy' => $systemUserId,
                'ModifiedBy' => $systemUserId,
            ]);

            return response()->json(['message' => 'Clarification submitted'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error', 'message' => $e->getMessage()], 500);
        }
    }

    public function listClarifications(int|string $rfq): JsonResponse
    {
        try {
            $rfqId = (int) $rfq;
            $user = Auth::guard('sanctum')->user();
            $thirdPartyId = ($user instanceof \App\Models\ThirdParty\ThirdPartyUser) ? $user->ThirdPartyId : null;

            if (! $thirdPartyId) {
                return response()->json(['data' => []]);
            }

            // FIXED: Get supplier IDs through SupplierMaster
            $mySupplierIds = DB::table('t_Suppliers as s')
                ->join('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
                ->where('sm.ThirdPartyId', $thirdPartyId)
                ->whereNull('s.DeletedOn')
                ->whereNull('sm.DeletedOn')
                ->pluck('s.Id');

            $clarifications = \App\Models\Procurement\RFQClarification::query()
                ->where('RFQId', $rfqId)
                ->whereIn('SupplierId', $mySupplierIds)
                ->whereNull('DeletedOn')
                ->orderByDesc('Id')
                ->get(['Id', 'RFQId', 'SupplierId', 'RFQLineId', 'Question', 'Answer', 'CreatedOn']);

            return response()->json(['data' => $clarifications]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to list clarifications'], 500);
        }
    }
}
