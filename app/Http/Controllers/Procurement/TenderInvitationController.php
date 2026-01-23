<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\TenderInvitation;
use App\Models\Procurement\Tender;
use App\Models\ThirdParies\ThirdParty;
use App\Models\ThirdParies\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
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

        $this->authorize('viewAny', TenderInvitation::class);

        // Otherwise return the web view
        return view('procurement.tendering.suppliermanagement.invitationresponsetracking.index');
    }

    public function storeResponse(Request $request)
    {
        // Public/Supplier facing usually, but if internal:
        // $this->authorize('create', TenderInvitation::class);
        // Assuming this is used by the system or suppliers, we might need a specific permission or leave open if it's a public endpoint protected by other means?
        // Checking controller logic, it seems mixed. For now, let's secure it.
        $this->authorize('create', TenderInvitation::class);

        $validated = $request->validate([
            'TenderId' => 'required|integer',
            'SupplierId' => 'required|integer',
            'ResponseStatus' => 'required|in:Pending,Accepted,Declined',
            'DeclineReason' => 'nullable|string',
            'ConfirmationAttachment' => 'nullable|file|max:2048',
        ]);

        // Check for existing response
        $existing = TenderInvitation::where('TenderId', $validated['TenderId'])
            ->where('SupplierId', $validated['SupplierId'])
            ->exists();

        if ($existing) {
             return redirect()->back()->with('error', 'You have already responded to this tender invitation.');
        }

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
            $page = (int)$request->query('page', 1);
            $limit = (int)$request->query('limit', 10);
            $user = Auth::guard('sanctum')->user();

            // Fallback to Auth user if query param is missing
            if (!$thirdPartyId && $user instanceof \App\Models\ThirdParty\ThirdPartyUser) {
                $thirdPartyId = $user->ThirdPartyId;
            }

            if (!$thirdPartyId) {
                return response()->json([
                    'error' => 'Third Party ID is required'
                ], 400);
            }

            // Get all supplier IDs for this third party (some have multiple supplier rows)
            // FIXED: Use direct DB Join to correctly resolve Supplier from ThirdParty via SupplierMaster
            // Previous code queried SupplierMaster.Id instead of SupplierMaster.ThirdPartyId
            $supplierIds = DB::table('t_Suppliers')
                ->join('t_SupplierMaster', 't_Suppliers.SupplierMasterId', '=', 't_SupplierMaster.Id')
                ->where('t_SupplierMaster.ThirdPartyId', (int)$thirdPartyId)
                ->whereNull('t_Suppliers.DeletedOn')
                ->pluck('t_Suppliers.Id');

            if ($supplierIds->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'total' => 0,
                    'page' => $page,
                    'limit' => $limit,
                    'supplierInfo' => [
                        'supplierId' => null,
                        'thirdPartyId' => (int)$thirdPartyId,
                    ]
                ]);
            }

            // Fetch tender invitations for these suppliers


            try {
                // Eager load items and prices for calculation
                $invitationsQuery = TenderInvitation::with(['tender.currency', 'tender.items.item.price', 'tender.tenderCategoryRelation', 'tender.documents'])
                    ->whereIn('SupplierId', $supplierIds)
                    ->whereNull('DeletedOn')
                    ->orderBy('InvitationDate', 'desc');

                // Apply pagination
                $offset = ($page - 1) * $limit;
                $total = (clone $invitationsQuery)->count();
                $invitations = $invitationsQuery->skip($offset)->take($limit)->get();
            } catch (\Exception $e) {
                Log::error('Error querying invitations', [
                    'supplier_ids' => $supplierIds->values()->all(),
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'message' => 'Failed to fetch invitations. Please try again later.'
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

                    // Calculate estimated cost dynamically from items to match backend view logic
                    $calculatedEstimatedValue = $invitation->tender->items->sum(function ($item) {
                        return ($item->QtyToTender ?? 0) * ($item->item?->price?->ActualPrice ?? 0);
                    });

                    // Use calculated value if available (and non-zero), otherwise fallback to column
                    $finalEstimatedValue = $calculatedEstimatedValue > 0
                        ? $calculatedEstimatedValue
                        : ($invitation->tender->EstimatedValue ?? 0);

                    $tenderData = [
                        'id' => (int)$invitation->tender->Id, // Ensure integer for matching
                        'tenderNo' => $invitation->tender->TenderNo ?? '',
                        'title' => $invitation->tender->Title ?? 'Untitled Tender',
                        'scopeOfWork' => $invitation->tender->ScopeOfWork ?? null,
                        'instructions' => $invitation->tender->Instructions ?? null,
                        'tenderType' => $invitation->tender->TenderType ?? 'rs',
                        'submissionDeadline' => $invitation->tender->SubmissionDeadline ?? null,
                        'openingDate' => $invitation->tender->OpeningDate ?? null,
                        'status' => $invitation->tender->Status ?? 'dr',
                        'estimatedValue' => $finalEstimatedValue,
                        'currency' => $invitation->tender->currency ? [
                            'code' => $invitation->tender->currency->Code,
                            'symbol' => $invitation->tender->currency->Symbol
                        ] : null,
                        'tenderCategoryRelation' => $invitation->tender->tenderCategoryRelation ? [
                            'tenderCategory' => $invitation->tender->tenderCategoryRelation->TenderCategory
                        ] : null,
                        'documents' => $invitation->tender->documents ? $invitation->tender->documents->map(function ($doc) {
                            return [
                                'id' => $doc->Id,
                                'fileName' => $doc->Name,
                                'extension' => $doc->Extension,
                                'fileSize' => $doc->Size, // Assuming Size attribute exists, otherwise null
                                'module' => $doc->Module,
                                'createdOn' => $doc->CreatedOn,
                            ];
                        }) : [],
                    ];
                } else {
                    // Fallback tender data if relationship fails
                    $tenderData = [
                        'id' => (int)$invitation->TenderId,
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
                        'TenderId' => (int)$invitation->TenderId, // Ensure integer for matching
                        'SupplierId' => $invitation->SupplierId,
                        'ResponseStatus' => strtolower($invitation->ResponseStatus ?? 'pending'), // Convert to lowercase
                        'ResponseDate' => $invitation->ResponseDate,
                        'DeclineReason' => $invitation->DeclineReason,
                        'InvitationDate' => $invitation->InvitationDate,
                    ],
                    'tender' => $tenderData,
                ];
            });

            return response()->json([
                'data' => $formattedData,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'supplierInfo' => [
                    'supplierIds' => $supplierIds->values()->all(),
                    'thirdPartyId' => (int)$thirdPartyId,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching tender invitations', [
                'error' => $e->getMessage(),
                'third_party_id' => $request->query('third_party_id')
            ]);

            return response()->json([
                'message' => 'An unexpected error occurred. Please try again later.'
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

            // Try to read from database
            try {
                $invitation = DB::table('t_TenderInvitations')
                    ->where('InvitationID', $id)
                    ->first();
            } catch (\Exception $readEx) {
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

            try {
                $currentUser = Auth::guard('sanctum')->user();
                
                if (!$currentUser) {
                    $currentUser = Auth::user();
                }

                if (!$currentUser) {
                     return response()->json(['error' => 'Unauthenticated'], 401);
                }

                $userId = $currentUser->getAuthIdentifier();
                $isThirdParty = $currentUser instanceof \App\Models\ThirdParty\ThirdPartyUser;


                // FK Fix: Use System Admin (1) for ThirdParty users
                if ($isThirdParty) {
                    $userId = 1; 
                }

                if (empty($userId)) {
                    $userId = 1;
                }

                DB::update(
                    'update t_TenderInvitations set ResponseStatus = ?, ResponseDate = ?, ModifiedBy = ? where InvitationID = ?',
                    [
                        ucfirst($validated['responseStatus']),
                        now(), 
                        $userId, 
                        $id
                    ]
                );
                
                $invitation->ResponseStatus = ucfirst($validated['responseStatus']);
                $invitation->ResponseDate = now();
                // $invitation->save(); // We used direct DB update above to bypass model issues


                // Harmonize with storeResponse: Use PascalCase for status
                $statusMap = [
                    'accepted' => 'Accepted',
                    'declined' => 'Declined',
                    'pending' => 'Pending',
                    'submitted' => 'Submitted'
                ];
                $cleanStatus = strtolower($validated['responseStatus']);
                $dbStatus = $statusMap[$cleanStatus] ?? ucfirst($cleanStatus);

                $updateData = [
                    'ResponseStatus' => $dbStatus,
                    'ResponseDate' => now(),
                    // Don't set ModifiedBy for supplier portal responses to avoid FK constraint issues
                    // ModifiedBy references t_Users, but Auth::user() is ThirdPartyUser
                    'ModifiedBy' => 1
                ];

                // Add decline reason only if provided and we're declining
                if (
                    $cleanStatus === 'declined' &&
                    !empty($validated['declineReason'])
                ) {
                    $updateData['DeclineReason'] = $validated['declineReason'];
                }

                $affected = DB::table('t_TenderInvitations')
                    ->where('InvitationID', $id)
                    ->update($updateData);
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
