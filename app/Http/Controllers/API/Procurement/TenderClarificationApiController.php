<?php

namespace App\Http\Controllers\API\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\TenderClarifications\ListPendingTenderClarificationsRequest;
use App\Http\Requests\Procurement\TenderClarifications\ListTenderClarificationsRequest;
use App\Http\Requests\Procurement\TenderClarifications\RespondTenderClarificationRequest;
use App\Http\Requests\Procurement\TenderClarifications\StoreTenderClarificationRequest;
use App\Http\Resources\Procurement\TenderClarificationResource;
use App\Models\Procurement\Tender;
use App\Models\Procurement\VendorClarifications;
use App\Models\ThirdParies\Supplier;
use App\Enums\TenderStatusEnum;
use App\Enums\TenderTypeEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenderClarificationApiController extends Controller
{
    public function submitClarification(StoreTenderClarificationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        try {
            // Resolve supplier
            $supplier = null;
            $user = Auth::guard('third_party')->user()
                ?? Auth::guard('sanctum')->user()
                ?? Auth::user();

            // Case 1: Third Party ID provided (e.g. from internal ERP call or debug)
            if (! empty($validated['third_party_id'])) {
                $supplierId = $this->resolveSupplierId((int)$validated['third_party_id']);
                $supplier = $supplierId ? (object)['Id' => $supplierId] : null;
            }
            // Case 2: Resolve from Authenticated User (Portal)
            elseif ($user) {
                $thirdPartyId = null;
                if ($user instanceof \App\Models\ThirdParty\ThirdPartyUser) {
                    $thirdPartyId = $user->ThirdPartyId ?? null;
                } elseif (property_exists($user, 'ThirdPartyId')) {
                    $thirdPartyId = $user->ThirdPartyId ?? null;
                } else {
                    $thirdPartyUser = DB::table('t_ThirdPartyUsers')->where('Id', $user->Id)->first();
                    $thirdPartyId = $thirdPartyUser->ThirdPartyId ?? null;
                }

                if ($thirdPartyId) {
                    $supplierId = $this->resolveSupplierId((int)$thirdPartyId);
                    $supplier = $supplierId ? (object)['Id' => $supplierId] : null;
                }
            }

            if (! $supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier record not found for the current user/context.',
                    'error' => 'Supplier record not found for the current user/context.',
                ], 404);
            }

            $tender = Tender::find($validated['tender_id']);
            if (! $tender) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tender not found.',
                    'error' => 'Tender not found.',
                ], 404);
            }

            if ($this->isRestrictedTender($tender)) {
                $invitation = DB::table('t_TenderInvitations')
                    ->where('TenderId', $validated['tender_id'])
                    ->where('SupplierId', $supplier->Id)
                    ->whereRaw('LOWER(ResponseStatus) = ?', ['accepted'])
                    ->first();

                if (! $invitation) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You must accept the tender invitation before asking questions.',
                        'error' => 'Access Denied',
                    ], 403);
                }
            } else {
                if (! $this->isTenderPublished($tender)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This tender is not open for clarifications.',
                        'error' => 'Access Denied',
                    ], 403);
                }
            }



            // Create the clarification using raw SQL with proper parameter binding
            // Note: CreatedBy and ModifiedBy must be bigint (user IDs), not strings
            // Get the first available user ID from the system
            $systemUser = DB::table('t_Users')->select('Id')->first();
            $systemUserId = $systemUser ? $systemUser->Id : 1; // Use first user or default to 1

            $sql = "INSERT INTO t_VendorClarifications (TenderID, VendorID, Question, QuestionDate, ISPUBLISHEDTOALL, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn)
                    OUTPUT INSERTED.ClarificationID
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"; // this is bad

            $result = DB::select($sql, [
                (int)$validated['tender_id'],
                (int)$supplier->Id,
                $validated['question'],
                date('Y-m-d H:i:s'),
                ! empty($validated['is_public']) ? 1 : 0,
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

            $clarification = VendorClarifications::with(['tenderID', 'vendorID'])
                ->find($clarificationId);

            if ($clarification) {
                $clarification->setAttribute('is_own_question', true);
                $clarification->setAttribute('status', 'pending');
            }

            return response()->json([
                'success' => true,
                'message' => 'Clarification Sent!',
                'data' => $clarification
                    ? (new TenderClarificationResource($clarification))->toArray($request)
                    : [
                        'clarificationId' => $clarificationId,
                        'tenderId' => $validated['tender_id'],
                        'question' => $validated['question'],
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
                'success' => false,
                'message' => 'Failed to submit clarification',
                'error' => $e->getMessage(),
                'debug' => $e->getTraceAsString(),
            ], 500);
        }
    }

    public function getClarifications(ListTenderClarificationsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        try {
            $user = Auth::guard('third_party')->user()
                ?? Auth::guard('sanctum')->user()
                ?? Auth::user();

            $supplier = null;
            if (! empty($validated['supplier_id'])) {
                $supplier = Supplier::find($validated['supplier_id']);
            } elseif (! empty($validated['third_party_id'])) {
                $supplierId = $this->resolveSupplierId((int)$validated['third_party_id']);
                $supplier = $supplierId ? Supplier::find($supplierId) : null;
            } elseif ($user) {
                $thirdPartyId = null;
                if ($user instanceof \App\Models\ThirdParty\ThirdPartyUser) {
                    $thirdPartyId = $user->ThirdPartyId ?? null;
                } elseif (property_exists($user, 'ThirdPartyId')) {
                    $thirdPartyId = $user->ThirdPartyId ?? null;
                } else {
                    $tpu = DB::table('t_ThirdPartyUsers')->where('Id', $user->Id)->first();
                    $thirdPartyId = $tpu->ThirdPartyId ?? null;
                }

                if ($thirdPartyId) {
                    $supplierId = $this->resolveSupplierId((int)$thirdPartyId);
                    $supplier = $supplierId ? Supplier::find($supplierId) : null;
                }
            }

            if (! $supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier context not found',
                    'error' => 'Supplier context not found',
                ], 404);
            }

            $tender = Tender::find($validated['tender_id']);
            if (! $tender) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tender not found.',
                    'error' => 'Tender not found.',
                ], 404);
            }

            if ($this->isRestrictedTender($tender)) {
                $invitation = DB::table('t_TenderInvitations')
                    ->where('TenderId', $validated['tender_id'])
                    ->where('SupplierId', $supplier->Id)
                    ->whereRaw('LOWER(ResponseStatus) = ?', ['accepted'])
                    ->first();

                if (! $invitation) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You must accept the tender invitation to view clarifications.',
                        'error' => 'Access Denied',
                    ], 403);
                }
            } else {
                if (! $this->isTenderPublished($tender)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This tender is not open for clarifications.',
                        'error' => 'Access Denied',
                    ], 403);
                }
            }

            // Get clarifications for this tender and supplier
            $clarifications = VendorClarifications::with(['tenderID', 'vendorID'])
                ->where('TenderID', $validated['tender_id'])
                ->where(function ($query) use ($supplier) {
                    // Get clarifications from this supplier OR public clarifications
                    $query->where('VendorID', $supplier->Id)
                        ->orWhere('ISPUBLISHEDTOALL', true);
                })
                ->whereNull('DeletedOn')
                ->orderBy('QuestionDate', 'desc')
                ->get();

            $clarifications->each(function ($clarification) use ($supplier) {
                $clarification->setAttribute('is_own_question', $clarification->VendorID == $supplier->Id);
                $clarification->setAttribute('status', $clarification->Answer ? 'answered' : 'pending');
            });

            return response()->json([
                'success' => true,
                'message' => 'Clarifications retrieved successfully.',
                'data' => TenderClarificationResource::collection($clarifications)->toArray($request),
                'total' => $clarifications->count(),
                'tender_id' => $validated['tender_id'],
                'supplier_id' => $supplier->Id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching tender clarifications', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch clarifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPendingClarifications(ListPendingTenderClarificationsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        try {
            $query = VendorClarifications::query()
                ->whereNull('Answer')
                ->whereNull('DeletedOn');

            if (! empty($validated['tender_id'])) {
                $query->where('TenderID', $validated['tender_id']);
            }

            if (! empty($validated['supplier_id'])) {
                $query->where('VendorID', $validated['supplier_id']);
            } elseif (! empty($validated['third_party_id'])) {
                $supplierId = $this->resolveSupplierId((int)$validated['third_party_id']);
                if ($supplierId) {
                    $query->where('VendorID', $supplierId);
                }
            }

            $data = $query->orderBy('QuestionDate', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Pending clarifications retrieved successfully.',
                'data' => TenderClarificationResource::collection($data)->toArray($request),
                'total' => $data->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching pending tender clarifications', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending clarifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function resolveSupplierId(int $thirdPartyId): ?int
    {
        return DB::table('t_Suppliers')
            ->join('t_SupplierMaster', 't_Suppliers.SupplierMasterId', '=', 't_SupplierMaster.Id')
            ->where('t_SupplierMaster.ThirdPartyId', $thirdPartyId)
            ->whereNull('t_Suppliers.DeletedOn')
            ->value('t_Suppliers.Id');
    }

    private function isRestrictedTender(Tender $tender): bool
    {
        $type = strtolower(trim((string) $tender->getRawOriginal('TenderType')));

        return in_array($type, [
            TenderTypeEnum::Restricted->value,
            'restricted',
            'restricted tender',
            'rs',
        ], true);
    }

    private function isTenderPublished(Tender $tender): bool
    {
        $status = strtolower(trim((string) $tender->getRawOriginal('Status')));

        return in_array($status, [
            TenderStatusEnum::Published->value,
            'published',
            TenderStatusEnum::OpeningInProgress->value,
            'opening in progress',
            'openinginprogress',
        ], true);
    }

    /**
     * Submit a response to a clarification from ERP staff
     * PUT /api/tender-clarifications/{id}/respond
     */
    public function respondToClarification(RespondTenderClarificationRequest $request, $clarificationId): JsonResponse
    {
        $validated = $request->validated();
        try {
            $clarification = VendorClarifications::find($clarificationId);

            if (! $clarification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Clarification not found',
                    'error' => 'Clarification not found',
                ], 404);
            }

            if ($clarification->Answer) {
                return response()->json([
                    'success' => false,
                    'message' => 'This clarification has already been answered',
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
                    $validated['answer'],
                    date('Y-m-d H:i:s'),
                    ! empty($validated['is_published_to_all']) ? 1 : 0,
                    $systemUserId,
                    date('Y-m-d H:i:s'),
                    $clarificationId,
                ]
            );

            Log::info('Clarification response submitted', [
                'clarification_id' => $clarificationId,
                'answered_by' => $validated['responded_by'],
                'is_public' => $validated['is_published_to_all'] ?? false,
            ]);

            $updated = VendorClarifications::with(['tenderID', 'vendorID'])->find($clarificationId);
            if ($updated) {
                $updated->setAttribute('status', $updated->Answer ? 'answered' : 'pending');
            }

            return response()->json([
                'success' => true,
                'message' => 'Response submitted successfully',
                'data' => $updated
                    ? (new TenderClarificationResource($updated))->toArray($request)
                    : [
                        'clarificationId' => $clarificationId,
                        'answer' => $validated['answer'],
                        'answerDate' => now()->format('Y-m-d H:i:s'),
                        'isPublic' => (bool)($validated['is_published_to_all'] ?? false),
                    ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error submitting clarification response', [
                'clarification_id' => $clarificationId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit response',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
