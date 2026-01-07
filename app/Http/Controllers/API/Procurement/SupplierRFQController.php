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
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'message' => 'You must be logged in to access this resource'
                ], 401);
            }
            
            // Check if user has ThirdPartyId
            $thirdPartyId = $user->ThirdPartyId ?? null;
            
            if (!$thirdPartyId) {
                Log::warning('Unauthorized access attempt to supplier RFQ', [
                    'user_id' => $user->id ?? $user->Id,
                    'user_type' => get_class($user)
                ]);
                
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'User is not associated with a Third Party'
                ], 403);
            }

            // Get supplier IDs
            $supplierIds = $this->rfqService->getSupplierIdsByThirdParty($thirdPartyId);
            
            if (empty($supplierIds)) {
                return response()->json([
                    'data' => [],
                    'message' => 'No active suppliers found for your account'
                ]);
            }

            // Get invitations
            $invitations = $this->rfqService->getInvitationsForSuppliers($supplierIds);

            return response()->json([
                'success' => true,
                'data' => $invitations,
                'meta' => [
                    'total' => count($invitations),
                    'supplier_count' => count($supplierIds)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching RFQ invitations', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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
        try {
            $rfqId = (int) $rfq;
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'Unauthenticated'
                ], 401);
            }

            $thirdPartyId = $user->ThirdPartyId ?? null;
            
            if (!$thirdPartyId) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'User is not associated with a Third Party'
                ], 403);
            }

            // Get supplier IDs
            $supplierIds = $this->rfqService->getSupplierIdsByThirdParty($thirdPartyId);
            
            if (empty($supplierIds)) {
                return response()->json([
                    'error' => 'Not Found',
                    'message' => 'No active suppliers found for your account'
                ], 404);
            }

            // Get detailed invitation
            $invitation = $this->rfqService->getDetailedInvitation($rfqId, $supplierIds);

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

            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'Unauthenticated'
                ], 401);
            }

            $thirdPartyId = $user->ThirdPartyId ?? null;
            
            if (!$thirdPartyId) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'User is not associated with a Third Party'
                ], 403);
            }

            // Submit response
            $result = $this->rfqService->submitResponse($validated, $thirdPartyId);

            return response()->json([
                'success' => true,
                'message' => $validated['isDraft'] ?? false 
                    ? 'Response saved as draft' 
                    : 'Response submitted successfully',
                'data' => $result
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation Error',
                'message' => 'Please check your input',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error submitting RFQ response', [
                'rfq_id' => $request->input('rfqId'),
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $statusCode = $e->getMessage() === 'Response already submitted' ? 409 : 500;

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

            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'Unauthenticated'
                ], 401);
            }

            $thirdPartyId = $user->ThirdPartyId ?? null;
            
            if (!$thirdPartyId) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'User is not associated with a Third Party'
                ], 403);
            }

            $result = $this->rfqService->submitClarification($validated, $thirdPartyId);

            return response()->json([
                'success' => true,
                'message' => 'Clarification submitted successfully',
                'data' => $result
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation Error',
                'message' => 'Please check your input',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error submitting clarification', [
                'rfq_id' => $request->input('rfqId'),
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get clarifications for an RFQ
     */
    public function listClarifications(int|string $rfq): JsonResponse
    {
        try {
            $rfqId = (int) $rfq;
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'Unauthenticated'
                ], 401);
            }

            $thirdPartyId = $user->ThirdPartyId ?? null;
            
            if (!$thirdPartyId) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'User is not associated with a Third Party'
                ], 403);
            }

            $supplierIds = $this->rfqService->getSupplierIdsByThirdParty($thirdPartyId);
            
            if (empty($supplierIds)) {
                return response()->json([
                    'data' => [],
                    'message' => 'No active suppliers found'
                ]);
            }

            $clarifications = $this->rfqService->getClarifications($rfqId, $supplierIds);

            return response()->json([
                'success' => true,
                'data' => $clarifications,
                'meta' => [
                    'total' => count($clarifications)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching clarifications', [
                'rfq_id' => $rfq,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Internal Server Error',
                'message' => 'An error occurred while fetching clarifications'
            ], 500);
        }
    }
}