<?php

namespace App\Http\Controllers\API\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Tender;
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
        try {
            // Simple validation first
            if (! $request->tender_id || ! $request->question) {
                return response()->json([
                    'error' => 'Missing required fields: tender_id, question',
                ], 422);
            }

            // Resolve supplier
            $supplier = null;
            $user = Auth::user();

            // Case 1: Third Party ID provided (e.g. from internal ERP call or debug)
            if ($request->third_party_id) {
                $supplier = DB::table('t_Suppliers')
                    ->join('t_SupplierMaster', 't_Suppliers.SupplierMasterId', '=', 't_SupplierMaster.Id')
                    ->where('t_SupplierMaster.ThirdPartyId', $request->third_party_id)
                    ->select('t_Suppliers.Id')
                    ->first();
            }
            // Case 2: Resolve from Authenticated User (Portal)
            elseif ($user) {
                // Check if user is a ThirdPartyUser and has a related ThirdParty
                // Note: Logic depends on how User model relates to ThirdParty
                // Assuming standard ThirdPartyUser model pattern where we can find the ThirdParty

                // First try direct relation if available
                if (method_exists($user, 'thirdParty')) {
                    $thirdPartyId = $user->thirdParty->Id ?? null;
                } else {
                    // Fallback to checking via email or other linking logic if needed
                    // For now, let's assume the user IS linked.
                    // This part might need adjustment based on specific User/ThirdPartyUser model structure
                    // Using a common pattern seen in other controllers:
                    $thirdPartyUser = DB::table('t_ThirdPartyUsers')->where('Id', $user->Id)->first();
                    $thirdPartyId = $thirdPartyUser->ThirdPartyId ?? null;
                }

                if ($thirdPartyId) {
                    $supplier = DB::table('t_Suppliers')
                        ->join('t_SupplierMaster', 't_Suppliers.SupplierMasterId', '=', 't_SupplierMaster.Id')
                        ->where('t_SupplierMaster.ThirdPartyId', $thirdPartyId)
                        // Getting the supplier ID linked to this third party
                        ->select('t_Suppliers.Id')
                        ->first();
                }
            }

            if (! $supplier) {
                return response()->json([
                    'error' => 'Supplier record not found for the current user/context.',
                ], 404);
            }

            // NEW: Enforce that the supplier has INVITED and ACCEPTED status for this tender
            $invitation = DB::table('t_TenderInvitations')
                ->where('TenderId', $request->tender_id)
                ->where('SupplierId', $supplier->Id)
                ->where('ResponseStatus', 'accepted') // Case-sensitive check matched to DB update method
                ->first();

            if (! $invitation) {
                return response()->json([
                    'error' => 'Access Denied',
                    'message' => 'You must accept the tender invitation before asking questions.',
                ], 403);
            }



            // Create the clarification using raw SQL with proper parameter binding
            // Note: CreatedBy and ModifiedBy must be bigint (user IDs), not strings
            // Get the first available user ID from the system
            $systemUser = DB::table('t_Users')->select('Id')->first();
            $systemUserId = $systemUser ? $systemUser->Id : 1; // Use first user or default to 1

            $sql = "INSERT INTO t_VendorClarifications (TenderID, VendorID, Question, QuestionDate, ISPUBLISHEDTOALL, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn)
                    OUTPUT INSERTED.ClarificationID
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $result = DB::select($sql, [
                (int)$request->tender_id,
                (int)$supplier->Id,
                $request->question,
                date('Y-m-d H:i:s'),
                $request->is_public ? 1 : 0,
                $systemUserId, // Use numeric user ID
                date('Y-m-d H:i:s'),
                $systemUserId, // Use numeric user ID
                date('Y-m-d H:i:s'),
            ]);

            $clarificationId = $result[0]->ClarificationID ?? null;

            if (! $clarificationId) {
                throw new \Exception('Failed to create clarification record');
            }

            Log::info('Created clarification', ['clarification_id' => $clarificationId]);

            return response()->json([
                'message' => 'Clarification submitted successfully',
                'data' => [
                    'clarificationId' => $clarificationId,
                    'tenderId' => $request->tender_id,
                    'question' => $request->question,
                    'questionDate' => date('Y-m-d H:i:s'),
                    'status' => 'pending',
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error submitting tender clarification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'error' => 'Failed to submit clarification',
                'message' => $e->getMessage(),
                'debug' => $e->getTraceAsString(),
            ], 500);
        }

    }

    public function getClarifications(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'tender_id' => 'required|integer|exists:t_Tenders,Id',
                'third_party_id' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors(),
                ], 422);
            }

            $hasAccess = DB::table('t_TenderInvitations')
                ->where('TenderId', $data['tender_id'])
                ->where('SupplierId', $supplier->Id)
                ->whereNull('DeletedOn')
                ->whereRaw('LOWER(ResponseStatus) = ?', ['accepted'])
                ->exists();

            if ($request->third_party_id) {
                $supplier = Supplier::whereHas('supplierMaster', function ($query) use ($request) {
                    $query->where('ThirdPartyId', $request->third_party_id);
                })->first();
            } elseif ($user) {
                // Try to find the supplier via the authenticated user's third party
                // Assuming the User model (likely ThirdPartyUser) has a way to get to ThirdParty
                $thirdPartyId = null;

                // Direct check on user object if loaded
                if (isset($user->ThirdPartyId)) {
                    $thirdPartyId = $user->ThirdPartyId;
                } else {
                    // Look up in t_ThirdPartyUsers
                    $tpu = DB::table('t_ThirdPartyUsers')->where('Id', $user->Id)->first();
                    $thirdPartyId = $tpu->ThirdPartyId ?? null;
                }

                if ($thirdPartyId) {
                    $supplier = Supplier::whereHas('supplierMaster', function ($query) use ($thirdPartyId) {
                        $query->where('ThirdPartyId', $thirdPartyId);
                    })->first();
                }
            }

            if (! $supplier) {
                return response()->json([
                    'error' => 'Supplier context not found',
                ], 404);
            }

            // NEW: Enforce that the supplier has INVITED and ACCEPTED status for this tender
            $invitation = DB::table('t_TenderInvitations')
                ->where('TenderId', $request->tender_id)
                ->where('SupplierId', $supplier->Id)
                ->where('ResponseStatus', 'accepted')
                ->first();

            if (! $invitation) {
                return response()->json([
                    'error' => 'Access Denied',
                    'message' => 'You must accept the tender invitation to view clarifications.',
                ], 403);
            }

            // Get clarifications for this tender and supplier
            $clarifications = VendorClarifications::with(['tenderID', 'vendorID'])
                ->where('TenderID', $request->tender_id)
                ->where(function ($query) use ($supplier) {
                    // Get clarifications from this supplier OR public clarifications
                    $query->where('VendorID', $supplier->Id)
                        ->orWhere('ISPUBLISHEDTOALL', true);
                })
                ->whereNull('DeletedOn')
                ->orderBy('QuestionDate', 'desc')
                ->get();

            // Format the response
            $formattedClarifications = $clarifications->map(function ($clarification) use ($supplier) {
                return [
                    'clarificationId' => $clarification->ClarificationID,
                    'tenderId' => $clarification->TenderID,
                    'question' => $clarification->Question,
                    'questionDate' => $clarification->QuestionDate,
                    'answer' => $clarification->Answer,
                    'answerDate' => $clarification->AnswerDate,
                    'isPublic' => (bool)$clarification->ISPUBLISHEDTOALL,
                    'isOwnQuestion' => $clarification->VendorID == $supplier->Id,
                    'status' => $clarification->Answer ? 'answered' : 'pending',
                    'createdBy' => $clarification->CreatedBy,
                    'createdOn' => $clarification->CreatedOn,
                ];
            });

            return response()->json([
                'data' => $formattedClarifications,
                'total' => $formattedClarifications->count(),
                'tender_id' => $request->tender_id,
                'supplier_id' => $supplier->Id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching tender clarifications', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'error' => 'Failed to fetch clarifications',
                'message' => $e->getMessage(),
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
                'responded_by' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors(),
                ], 422);
            }

            $clarification = VendorClarifications::find($clarificationId);

            if (! $clarification) {
                return response()->json([
                    'error' => 'Clarification not found',
                ], 404);
            }

            if ($clarification->Answer) {
                return response()->json([
                    'error' => 'This clarification has already been answered',
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
                    $clarificationId,
                ]
            );

            Log::info('Clarification response submitted', [
                'clarification_id' => $clarificationId,
                'answered_by' => $request->responded_by,
                'is_public' => $request->is_published_to_all ?? false,
            ]);

            return response()->json([
                'message' => 'Response submitted successfully',
                'data' => [
                    'clarificationId' => $clarificationId,
                    'answer' => $request->answer,
                    'answerDate' => now()->format('Y-m-d H:i:s'),
                    'isPublic' => (bool)($request->is_published_to_all ?? false),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error submitting clarification response', [
                'clarification_id' => $clarificationId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to submit response',
                'message' => $e->getMessage(),
            ], 500);
        }

    }
}
