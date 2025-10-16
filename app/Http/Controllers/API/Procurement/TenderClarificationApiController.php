<?php

namespace App\Http\Controllers\API\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\VendorClarifications;
use App\Models\Procurement\Tender;
use App\Models\ThirdParies\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TenderClarificationApiController extends Controller
{
    /**
     * Submit a clarification question from the portal
     * POST /api/tender-clarifications
     */
    public function submitClarification(Request $request): JsonResponse
    {
        try {
            // Simple validation first
            if (!$request->tenderId || !$request->question || !$request->third_party_id) {
                return response()->json([
                    'error' => 'Missing required fields: tenderId, question, third_party_id'
                ], 422);
            }

            // Log the request for debugging
            Log::info('Clarification submission attempt', [
                'request_data' => $request->all()
            ]);

            // Test DB access - Get supplier ID from third party ID
            $supplier = DB::table('t_Suppliers')
                ->join('t_ThirdParties', 't_Suppliers.ThirdPartyID', '=', 't_ThirdParties.Id')
                ->where('t_ThirdParties.Id', $request->third_party_id)
                ->select('t_Suppliers.Id')
                ->first();

            if (!$supplier) {
                return response()->json([
                    'error' => 'Supplier not found for third party ID: ' . $request->third_party_id
                ], 404);
            }

            Log::info('Found supplier', ['supplier_id' => $supplier->Id]);

            // Create the clarification using raw SQL with proper parameter binding
            // Note: CreatedBy and ModifiedBy must be bigint (user IDs), not strings
            // Get the first available user ID from the system
            $systemUser = DB::table('t_Users')->select('Id')->first();
            $systemUserId = $systemUser ? $systemUser->Id : 1; // Use first user or default to 1
            
            $sql = "INSERT INTO t_VendorClarifications (TenderID, VendorID, Question, QuestionDate, ISPUBLISHEDTOALL, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn) 
                    OUTPUT INSERTED.ClarificationID
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $result = DB::select($sql, [
                (int) $request->tenderId,
                (int) $supplier->Id,
                $request->question,
                date('Y-m-d H:i:s'),
                0,
                $systemUserId, // Use numeric user ID
                date('Y-m-d H:i:s'),
                $systemUserId, // Use numeric user ID
                date('Y-m-d H:i:s'),
            ]);
            
            $clarificationId = $result[0]->ClarificationID ?? null;
            
            if (!$clarificationId) {
                throw new \Exception('Failed to create clarification record');
            }

            Log::info('Created clarification', ['clarification_id' => $clarificationId]);

            return response()->json([
                'message' => 'Clarification submitted successfully',
                'data' => [
                    'clarificationId' => $clarificationId,
                    'tenderId' => $request->tenderId,
                    'question' => $request->question,
                    'questionDate' => date('Y-m-d H:i:s'),
                    'status' => 'pending'
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error submitting tender clarification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Failed to submit clarification',
                'message' => $e->getMessage(),
                'debug' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Get clarifications for a specific tender and supplier
     * GET /api/tender-clarifications?tender_id=X&third_party_id=Y
     */
    public function getClarifications(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'tender_id' => 'required|integer|exists:t_Tenders,Id',
                'third_party_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }

            // Get supplier ID from third party ID
            $supplier = Supplier::whereHas('thirdParty', function($query) use ($request) {
                $query->where('Id', $request->third_party_id);
            })->first();

            if (!$supplier) {
                return response()->json([
                    'error' => 'Supplier not found'
                ], 404);
            }

            // Get clarifications for this tender and supplier
            $clarifications = VendorClarifications::with(['tenderID', 'vendorID'])
                ->where('TenderID', $request->tender_id)
                ->where(function($query) use ($supplier) {
                    // Get clarifications from this supplier OR public clarifications
                    $query->where('VendorID', $supplier->Id)
                          ->orWhere('ISPUBLISHEDTOALL', true);
                })
                ->whereNull('DeletedOn')
                ->orderBy('QuestionDate', 'desc')
                ->get();

            // Format the response
            $formattedClarifications = $clarifications->map(function($clarification) use ($supplier) {
                return [
                    'clarificationId' => $clarification->ClarificationID,
                    'tenderId' => $clarification->TenderID,
                    'question' => $clarification->Question,
                    'questionDate' => $clarification->QuestionDate,
                    'answer' => $clarification->Answer,
                    'answerDate' => $clarification->AnswerDate,
                    'isPublic' => (bool) $clarification->ISPUBLISHEDTOALL,
                    'isOwnQuestion' => $clarification->VendorID == $supplier->Id,
                    'status' => $clarification->Answer ? 'answered' : 'pending',
                    'createdBy' => $clarification->CreatedBy,
                    'createdOn' => $clarification->CreatedOn
                ];
            });

            return response()->json([
                'data' => $formattedClarifications,
                'total' => $formattedClarifications->count(),
                'tender_id' => $request->tender_id,
                'supplier_id' => $supplier->Id
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching tender clarifications', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Failed to fetch clarifications',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all pending clarifications for ERP staff to respond to
     * GET /api/tender-clarifications/pending
     */
    public function getPendingClarifications(Request $request): JsonResponse
    {
        try {
            $page = (int) $request->query('page', 1);
            $limit = (int) $request->query('limit', 20);
            $tenderId = $request->query('tender_id');

            $query = VendorClarifications::with(['tenderID', 'vendorID'])
                ->whereNull('Answer')
                ->whereNull('DeletedOn');

            // Filter by specific tender if requested
            if ($tenderId) {
                $query->where('TenderID', $tenderId);
            }

            $total = $query->count();
            $offset = ($page - 1) * $limit;
            
            $clarifications = $query->orderBy('QuestionDate', 'asc')
                ->skip($offset)
                ->take($limit)
                ->get();

            // Format the response with additional supplier information
            $formattedClarifications = $clarifications->map(function($clarification) {
                // Get supplier name from third party relationship
                $supplierName = 'Unknown Supplier';
                if ($clarification->vendorID && $clarification->vendorID->thirdParty) {
                    $supplierName = $clarification->vendorID->thirdParty->TradingName 
                                 ?? $clarification->vendorID->thirdParty->ThirdPartyName;
                }

                return [
                    'clarificationId' => $clarification->ClarificationID,
                    'tenderId' => $clarification->TenderID,
                    'tenderNo' => $clarification->tenderID->TenderNo ?? 'N/A',
                    'tenderTitle' => $clarification->tenderID->Title ?? 'N/A',
                    'vendorId' => $clarification->VendorID,
                    'supplierName' => $supplierName,
                    'question' => $clarification->Question,
                    'questionDate' => $clarification->QuestionDate,
                    'daysPending' => now()->diffInDays($clarification->QuestionDate),
                    'createdBy' => $clarification->CreatedBy,
                    'createdOn' => $clarification->CreatedOn
                ];
            });

            return response()->json([
                'data' => $formattedClarifications,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => ceil($total / $limit)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching pending clarifications', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to fetch pending clarifications',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit a response to a clarification from ERP staff
     * PUT /api/tender-clarifications/{id}/respond
     */
    public function respondToClarification(Request $request, $clarificationId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'answer' => 'required|string|min:10|max:2000',
                'is_published_to_all' => 'boolean',
                'responded_by' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }

            $clarification = VendorClarifications::find($clarificationId);

            if (!$clarification) {
                return response()->json([
                    'error' => 'Clarification not found'
                ], 404);
            }

            if ($clarification->Answer) {
                return response()->json([
                    'error' => 'This clarification has already been answered'
                ], 409);
            }

            // Get a valid user ID for ModifiedBy
            $systemUser = DB::table('t_Users')->select('Id')->first();
            $systemUserId = $systemUser ? $systemUser->Id : 1;

            // Update the clarification with response using raw SQL for SQL Server compatibility
            $affected = DB::update(
                "UPDATE t_VendorClarifications 
                 SET Answer = ?, AnswerDate = ?, ISPUBLISHEDTOALL = ?, ModifiedBy = ?, ModifiedOn = ?
                 WHERE ClarificationID = ?", 
                [
                    $request->answer,
                    date('Y-m-d H:i:s'),
                    $request->is_published_to_all ?? false ? 1 : 0,
                    $systemUserId,
                    date('Y-m-d H:i:s'),
                    $clarificationId
                ]
            );

            Log::info('Clarification response submitted', [
                'clarification_id' => $clarificationId,
                'answered_by' => $request->responded_by,
                'is_public' => $request->is_published_to_all ?? false
            ]);

            return response()->json([
                'message' => 'Response submitted successfully',
                'data' => [
                    'clarificationId' => $clarificationId,
                    'answer' => $request->answer,
                    'answerDate' => now()->format('Y-m-d H:i:s'),
                    'isPublic' => (bool) ($request->is_published_to_all ?? false)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error submitting clarification response', [
                'clarification_id' => $clarificationId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to submit response',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
