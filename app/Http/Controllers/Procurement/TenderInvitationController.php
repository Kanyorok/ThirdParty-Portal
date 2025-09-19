<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\TenderInvitation;
use App\Models\Procurement\Tender;
use App\Models\ThirdParies\ThirdParty;
use App\Models\ThirdParies\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class TenderInvitationController extends Controller
{
    public function index(Request $request)
    {
        // If this is an API request (has Accept: application/json header)
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->getSupplierInvitations($request);
        }
        
        // Otherwise return the web view
        return view('procurement.tendering.suppliermanagement.invitationresponsetracking.index');
    }

    public function storeResponse(Request $request)
    {

        $validated = $request->validate([
            'TenderId' => 'required|integer',
            'SupplierId' => 'required|integer',
            'ResponseStatus' => 'required|in:Pending,Accepted,Declined',
            'DeclineReason' => 'nullable|string',
            'ConfirmationAttachment' => 'nullable|file|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('ConfirmationAttachment')) {
            $path = $request->file('ConfirmationAttachment')->store('attachments', 'public');
        }

        TenderInvitation::create([
            'TenderId' => $validated['TenderId'],
            'SupplierId' => $validated['SupplierId'],
            'InvitationDate' => now(),
            'ResponseStatus' => $validated['ResponseStatus'],
            'ResponseDate' => now(),
            'DeclineReason' => $validated['DeclineReason'] ?? null,
            'ConfirmationAttachment' => $path,
            'CreatedBy' => $request->user()->Id,
            'ModifiedBy' => $request->user()->Id,
        ]);
        return redirect()->back()->with('success', 'Your response has been recorded.');
    }

    // API Methods for Frontend Integration

    /**
     * Get tender invitations for supplier portal
     * Called by frontend: GET /api/tender-invitations?third_party_id=123
     */
    public function getSupplierInvitations(Request $request): JsonResponse
    {
        try {
            $thirdPartyId = $request->query('third_party_id');
            $page = (int) $request->query('page', 1);
            $limit = (int) $request->query('limit', 10);
            
            if (!$thirdPartyId) {
                return response()->json([
                    'error' => 'Third Party ID is required'
                ], 400);
            }

            // Get supplier ID from third party ID
            $supplier = Supplier::whereHas('thirdParty', function($query) use ($thirdPartyId) {
                $query->where('Id', $thirdPartyId);
            })->first();

            if (!$supplier) {
                return response()->json([
                    'error' => 'Supplier not found for this third party',
                    'debug' => [
                        'third_party_id' => $thirdPartyId,
                        'message' => 'No supplier record found for this third party ID'
                    ]
                ], 404);
            }

            // Fetch tender invitations for this supplier
            Log::info('Fetching invitations for supplier', ['supplier_id' => $supplier->Id]);
            
            try {
                $invitationsQuery = TenderInvitation::with(['tender'])
                    ->where('SupplierId', $supplier->Id)
                    ->orderBy('InvitationDate', 'desc');

                // Apply pagination
                $offset = ($page - 1) * $limit;
                $total = $invitationsQuery->count();
                $invitations = $invitationsQuery->skip($offset)->take($limit)->get();

                Log::info('Found invitations', [
                    'supplier_id' => $supplier->Id,
                    'total' => $total,
                    'returned' => $invitations->count()
                ]);

            } catch (\Exception $e) {
                Log::error('Error querying invitations', [
                    'supplier_id' => $supplier->Id,
                    'error' => $e->getMessage()
                ]);
                
                return response()->json([
                    'error' => 'Database query failed',
                    'message' => $e->getMessage(),
                    'debug' => [
                        'supplier_id' => $supplier->Id,
                        'third_party_id' => $thirdPartyId
                    ]
                ], 500);
            }

            // Initialize empty collection if no invitations found
            if (!isset($invitations)) {
                $invitations = collect([]);
            }

            // Format the response to match frontend expectations
            $formattedData = $invitations->map(function ($invitation) {
                $tenderData = null;
                
                // Safely access tender relationship
                if ($invitation->tender) {
                    $tenderData = [
                        'id' => (int) $invitation->tender->Id, // Ensure integer for matching
                        'tenderNo' => $invitation->tender->TenderNo ?? '',
                        'title' => $invitation->tender->Title ?? 'Untitled Tender',
                        'tenderType' => $invitation->tender->TenderType ?? 'rs',
                        'submissionDeadline' => $invitation->tender->SubmissionDeadline ?? null,
                        'openingDate' => $invitation->tender->OpeningDate ?? null,
                        'status' => $invitation->tender->Status ?? 'dr',
                        'estimatedValue' => $invitation->tender->EstimatedValue ?? 0,
                        'currency' => null, // Simplified for now to avoid relationship issues
                    ];
                } else {
                    // Fallback tender data if relationship fails
                    $tenderData = [
                        'id' => (int) $invitation->TenderId,
                        'tenderNo' => 'LOADING...',
                        'title' => 'Tender (Loading...)',
                        'tenderType' => 'rs',
                        'submissionDeadline' => null,
                        'openingDate' => null,
                        'status' => 'dr',
                        'estimatedValue' => 0,
                        'currency' => null,
                    ];
                }
                
                return [
                    'invitation' => [
                        'InvitationID' => $invitation->InvitationID,
                        'TenderId' => (int) $invitation->TenderId, // Ensure integer for matching
                        'SupplierId' => $invitation->SupplierId,
                        'ResponseStatus' => strtolower($invitation->ResponseStatus ?? 'pending'), // Convert to lowercase
                        'ResponseDate' => $invitation->ResponseDate,
                        'DeclineReason' => $invitation->DeclineReason,
                        'InvitationDate' => $invitation->InvitationDate,
                    ],
                    'tender' => $tenderData,
                ];
            });

            Log::info('API: Returning tender invitations', [
                'third_party_id' => $thirdPartyId,
                'supplier_id' => $supplier->Id,
                'invitations_count' => $invitations->count(),
                'total' => $total,
                'sample_data' => $formattedData->take(1)
            ]);

            return response()->json([
                'data' => $formattedData,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'supplierInfo' => [
                    'supplierId' => $supplier->Id,
                    'thirdPartyId' => (int) $thirdPartyId,
                ],
                'debug' => [
                    'message' => 'Successfully fetched tender invitations',
                    'supplier_found' => true,
                    'invitations_found' => $invitations->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching tender invitations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'third_party_id' => $request->query('third_party_id')
            ]);

            return response()->json([
                'error' => 'Failed to fetch tender invitations',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update invitation response
     * Called by frontend: PUT /api/tender-invitations/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'responseStatus' => 'required|in:accepted,declined,pending',
                'declineReason' => 'nullable|string',
            ]);

            // Test 1: Basic validation and logging
            Log::info('=== STEP 1: Validation successful ===', [
                'invitation_id' => $id,
                'request_data' => $validated
            ]);

            // Test 2: Try to read from database
            try {
                $invitation = DB::table('t_TenderInvitations')
                    ->where('InvitationID', $id)
                    ->first();
                    
                Log::info('=== STEP 2: Database read successful ===', [
                    'invitation_found' => $invitation ? true : false,
                    'current_status' => $invitation ? $invitation->ResponseStatus : 'N/A'
                ]);
            } catch (\Exception $readEx) {
                Log::error('=== STEP 2 FAILED: Database read error ===', [
                    'error' => $readEx->getMessage()
                ]);
                return response()->json([
                    'error' => 'Database read failed',
                    'message' => $readEx->getMessage()
                ], 500);
            }

            if (!$invitation) {
                return response()->json([
                    'error' => 'Invitation not found'
                ], 404);
            }

            // Working minimal update - just the essential fields
            try {
                Log::info('=== STEP 3: Attempting database update ===');
                
                // Start with just the status field that we know works
                $updateData = [
                    'ResponseStatus' => $validated['responseStatus']
                ];

                // Add decline reason only if provided and we're declining
                if ($validated['responseStatus'] === 'declined' && 
                    isset($validated['declineReason']) && 
                    !empty($validated['declineReason'])) {
                    $updateData['DeclineReason'] = $validated['declineReason'];
                }
                
                $affected = DB::table('t_TenderInvitations')
                    ->where('InvitationID', $id)
                    ->update($updateData);

                Log::info('=== STEP 3 SUCCESS: Database update completed ===', [
                    'affected_rows' => $affected,
                    'update_data' => $updateData
                ]);
            } catch (\Exception $updateEx) {
                Log::error('=== STEP 3 FAILED: Database update error ===', [
                    'error' => $updateEx->getMessage(),
                    'sql_state' => $updateEx->getCode()
                ]);
                return response()->json([
                    'error' => 'Database update failed',
                    'message' => $updateEx->getMessage(),
                    'sql_error' => true
                ], 500);
            }

            // Get the updated record to return current values
            $updatedInvitation = DB::table('t_TenderInvitations')
                ->where('InvitationID', $id)
                ->first();

            return response()->json([
                'message' => 'Invitation response updated successfully',
                'data' => [
                    'InvitationID' => $updatedInvitation->InvitationID,
                    'ResponseStatus' => $updatedInvitation->ResponseStatus,
                    'ResponseDate' => $updatedInvitation->ResponseDate,
                    'DeclineReason' => $updatedInvitation->DeclineReason,
                    'affected_rows' => $affected,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('=== GENERAL ERROR ===', [
                'invitation_id' => $id,
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'General failure',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
