<?php

namespace App\Http\Controllers\API\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQClarification;
use App\Models\Procurement\RFQLine;
use App\Models\Procurement\RFQResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SupplierRFQController extends Controller
{
    protected function thirdPartyUser()
    {
        return Auth::guard('third_party')->user()
            ?? Auth::guard('sanctum')->user()
            ?? Auth::user();
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

    protected function toDateString(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return (string)$value;
        }
    }

    protected function generateRFQResponseNumber(): string
    {
        $prefix = 'RFQRE-';

        $last = RFQResponse::where('RFQResponseNumber', 'like', $prefix . '%')
            ->orderByDesc('Id')
            ->first();

        $lastNumber = $last?->RFQResponseNumber
            ? (int)Str::after($last->RFQResponseNumber, $prefix)
            : 0;

        return $prefix . str_pad((string)($lastNumber + 1), 5, '0', STR_PAD_LEFT);
    }

    protected function supplierNameForSupplierId(int $supplierId): ?string
    {
        return DB::table('t_Suppliers as s')
            ->join('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
            ->leftJoin('t_ThirdParties as tp', 'sm.ThirdPartyId', '=', 'tp.Id')
            ->where('s.Id', $supplierId)
            ->whereNull('s.DeletedOn')
            ->whereNull('sm.DeletedOn')
            ->selectRaw("COALESCE(tp.TradingName, tp.ThirdPartyName) as SupplierName")
            ->value('SupplierName');
    }

    protected function normalizeSubmitResponsePayload(Request $request): void
    {
        $data = $request->all();
        $merge = [];

        if (! array_key_exists('rfqId', $data)) {
            $merge['rfqId'] = $data['RFQId'] ?? $data['rfq_id'] ?? $data['rfqID'] ?? null;
        }

        if (! array_key_exists('supplierId', $data)) {
            $merge['supplierId'] = $data['SupplierId'] ?? null;
        }

        if (! array_key_exists('currency', $data)) {
            $merge['currency'] = $data['Currency'] ?? null;
        }

        if (! array_key_exists('durationDays', $data)) {
            $merge['durationDays'] = $data['DurationDays'] ?? $data['duration_days'] ?? null;
        }

        if (! array_key_exists('isDraft', $data)) {
            $merge['isDraft'] = $data['IsDraft'] ?? null;
        }

        if (! array_key_exists('items', $data)) {
            $rawItems = $data['RequisitionItems'] ?? $data['items'] ?? null;

            if (is_array($rawItems)) {
                $merge['items'] = collect($rawItems)->map(function ($it) {
                    if (! is_array($it)) {
                        return $it;
                    }

                    return [
                        'rfqLineId' => $it['rfqLineId'] ?? $it['RFQLineId'] ?? $it['id'] ?? $it['Id'] ?? null,
                        'quotedPrice' => $it['quotedPrice'] ?? $it['quotedprice'] ?? $it['QuotedPrice'] ?? null,
                        'totalPayable' => $it['totalPayable'] ?? $it['totalpayable'] ?? $it['TotalPayable'] ?? null,
                    ];
                })->toArray();
            }
        }

        if (! empty($merge)) {
            $request->merge($merge);
        }
    }

    public function listInvitations(Request $request): JsonResponse
    {
        try {
            $user = $this->thirdPartyUser();

            if (! $user || ! isset($user->ThirdPartyId)) {
                return response()->json(['data' => []]);
            }

            $supplierIds = $this->supplierIdsForUser($user);

            if ($supplierIds->isEmpty()) {
                return response()->json(['data' => []]);
            }

            $invitations = DB::table('t_RFQ_Supplier as rs')
                ->join('t_RFQ as r', 'r.Id', '=', 'rs.RFQId')
                ->whereIn('rs.SupplierId', $supplierIds)
                ->whereNull('r.DeletedOn')
                ->select(
                    'r.Id as rfqId',
                    'r.RFQNumber as rfqNumber',
                    'r.Comments as comments',
                    'r.Status as status',
                    'r.SubmissionDeadline as submissionDeadline',
                    'rs.SupplierId as supplierId',
                    'rs.Status as invitationStatus'
                )
                ->orderByDesc('r.Id')
                ->get();

            $rfqIds = $invitations->pluck('rfqId')->unique()->values();

            $rfqs = RFQ::query()
                ->whereIn('Id', $rfqIds)
                ->with([
                    'rfqLines.uom',
                    'rfqLines.category',
                    'sections.section',
                    'criteria.criteria',
                    'criteria.section',
                    'requisition',
                    'statusDetail',
                ])
                ->get()
                ->keyBy('Id');

            $responses = RFQResponse::query()
                ->whereIn('RFQId', $rfqIds)
                ->whereIn('SupplierId', $supplierIds)
                ->whereNull('DeletedOn')
                ->with(['items.uom'])
                ->orderByDesc('Id')
                ->get()
                ->groupBy(fn ($r) => $r->RFQId . '|' . $r->SupplierId)
                ->map(fn ($group) => $group->first());

            $data = $invitations->map(function ($inv) use ($rfqs, $responses) {
                $rfq = $rfqs->get($inv->rfqId);
                $myResponse = $responses->get($inv->rfqId . '|' . $inv->supplierId);

                return [
                    'rfqId' => (string)$inv->rfqId,
                    'rfqNumber' => $inv->rfqNumber,
                    'comments' => $inv->comments,
                    'status' => $inv->status,
                    'submissionDeadline' => $this->toDateString($inv->submissionDeadline),
                    'supplierId' => (string)$inv->supplierId,
                    'invitationStatus' => $inv->invitationStatus,
                    'rfq' => $rfq,
                    'myResponse' => $myResponse,
                ];
            });

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

            if (! $user || ! isset($user->ThirdPartyId)) {
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
                    'r.Comments as comments',
                    'r.Status as status',
                    'r.SubmissionDeadline as submissionDeadline',
                    'rs.SupplierId as supplierId',
                    'rs.Status as invitationStatus',
                    'rs.CreatedOn',
                    'rs.ModifiedOn'
                )
                ->first();

            if (! $invitation) {
                return response()->json(['error' => 'Not Found'], 404);
            }

            $rfqModel = RFQ::query()
                ->where('Id', (int)$rfq)
                ->with([
                    'rfqLines.uom',
                    'rfqLines.category',
                    'sections.section',
                    'criteria.criteria',
                    'criteria.section',
                    'requisition',
                    'statusDetail',
                ])
                ->first();

            $myResponse = RFQResponse::query()
                ->where('RFQId', (int)$rfq)
                ->where('SupplierId', (int)$invitation->supplierId)
                ->whereNull('DeletedOn')
                ->with(['items.uom'])
                ->orderByDesc('Id')
                ->first();

            return response()->json([
                'data' => [
                    'rfqId' => (string)$invitation->rfqId,
                    'rfqNumber' => $invitation->rfqNumber,
                    'comments' => $invitation->comments,
                    'status' => $invitation->status,
                    'submissionDeadline' => $this->toDateString($invitation->submissionDeadline),
                    'supplierId' => (string)$invitation->supplierId,
                    'invitationStatus' => $invitation->invitationStatus,
                    'invitedOn' => $invitation->CreatedOn ?? null,
                    'invitationUpdatedOn' => $invitation->ModifiedOn ?? null,
                    'rfq' => $rfqModel,
                    'myResponse' => $myResponse,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('getInvitation failed', ['rfq' => $rfq, 'error' => $e->getMessage()]);

            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function submitResponse(Request $request): JsonResponse
    {
        try {
            $this->normalizeSubmitResponsePayload($request);

            $validated = $request->validate([
                'rfqId' => 'required|integer|exists:t_RFQ,Id',
                'supplierId' => 'sometimes|integer',
                'currency' => 'required|string|max:3',
                'durationDays' => 'required|integer|min:1',
                'items' => 'required|array|min:1',
                'items.*.rfqLineId' => 'required|integer|exists:t_RFQLines,Id',
                'items.*.quotedPrice' => 'required|numeric|min:0',
                'items.*.totalPayable' => 'required|numeric|min:0',
                'isDraft' => 'sometimes|boolean',
            ]);

            $user = $this->thirdPartyUser();

            if (! $user || ! isset($user->ThirdPartyId)) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            $supplierIds = $this->supplierIdsForUser($user);

            $supplierIdQuery = DB::table('t_RFQ_Supplier')
                ->where('RFQId', $validated['rfqId'])
                ->whereIn('SupplierId', $supplierIds);

            if (! empty($validated['supplierId'])) {
                $supplierIdQuery->where('SupplierId', (int)$validated['supplierId']);
            }

            $supplierId = $supplierIdQuery->value('SupplierId');

            if (! $supplierId) {
                return response()->json(['error' => 'No invitation found'], 403);
            }

            $rfqModel = RFQ::query()->where('Id', $validated['rfqId'])->first();

            if (! $rfqModel) {
                return response()->json(['error' => 'RFQ not found'], 404);
            }

            // Check Submission Deadline
            if ($rfqModel->SubmissionDeadline && \Carbon\Carbon::parse($rfqModel->SubmissionDeadline)->isPast()) {
                return response()->json([
                    'error' => 'The submission deadline for this RFQ has passed. Responses can no longer be submitted.',
                ], 422);
            }

            $rfqLineIds = collect($validated['items'])->pluck('rfqLineId')->unique()->values();

            $rfqLines = RFQLine::query()
                ->where('RFQId', $validated['rfqId'])
                ->whereIn('Id', $rfqLineIds)
                ->get()
                ->keyBy('Id');

            $missingLineIds = $rfqLineIds->reject(fn ($id) => $rfqLines->has($id))->values();
            if ($missingLineIds->isNotEmpty()) {
                return response()->json([
                    'error' => 'Invalid rfqLineId(s) for this rfqId',
                    'missingRfqLineIds' => $missingLineIds,
                ], 422);
            }

            $existing = RFQResponse::where('RFQId', $validated['rfqId'])
                ->where('SupplierId', $supplierId)
                ->whereNull('DeletedOn')
                ->first();

            if ($existing && strtoupper($existing->Status) === 'FINAL') {
                return response()->json(['error' => 'Already submitted'], 409);
            }

            DB::beginTransaction();

            $status = ($validated['isDraft'] ?? false) ? 'DRAFT' : 'FINAL';
            $totalPayable = collect($validated['items'])->sum('totalPayable');
            $supplierName = $this->supplierNameForSupplierId((int)$supplierId);

            $response = $existing ?? RFQResponse::create([
                'RFQId' => $validated['rfqId'],
                'RFQResponseNumber' => $this->generateRFQResponseNumber(),
                'RFQNumber' => $rfqModel->RFQNumber,
                'SupplierId' => $supplierId,
                'SupplierName' => $supplierName,
                'TotalPayable' => $totalPayable,
                'Currency' => $validated['currency'],
                'DurationDays' => $validated['durationDays'],
                'Status' => $status,
                'SubmittedOn' => now(),
                'CreatedBy' => $user->Id,
                'ModifiedBy' => $user->Id,
            ]);

            if ($existing) {
                $response->update([
                    'RFQResponseNumber' => $response->RFQResponseNumber ?: $this->generateRFQResponseNumber(),
                    'RFQNumber' => $response->RFQNumber ?: $rfqModel->RFQNumber,
                    'SupplierName' => $response->SupplierName ?: $supplierName,
                    'TotalPayable' => $totalPayable,
                    'Currency' => $validated['currency'],
                    'DurationDays' => $validated['durationDays'],
                    'Status' => $status,
                    'SubmittedOn' => now(),
                    'ModifiedBy' => $user->Id,
                ]);

                $response->items()->delete();
            }

            foreach ($validated['items'] as $item) {
                /** @var RFQLine $line */
                $line = $rfqLines->get($item['rfqLineId']);
                $response->items()->create([
                    'ItemName' => $line->ItemName,
                    'UOM' => $line->UOM,
                    'Quantity' => $line->Quantity,
                    'QuotedPrice' => $item['quotedPrice'],
                    'TotalPayable' => $item['totalPayable'],
                    'CreatedBy' => $user->Id,
                    'ModifiedBy' => $user->Id,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Response submitted successfully',
                'data' => [
                    'rfqResponseId' => $response->Id,
                    'status' => $response->Status,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
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
                'supplierId' => 'sometimes|integer',
                'question' => 'required|string|max:1000',
                'rfqLineId' => 'nullable|integer|exists:t_RFQLines,Id',
            ]);

            $user = $this->thirdPartyUser();

            if (! $user || ! isset($user->ThirdPartyId)) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            $supplierIds = $this->supplierIdsForUser($user);

            $supplierIdQuery = DB::table('t_RFQ_Supplier')
                ->where('RFQId', $validated['rfqId'])
                ->whereIn('SupplierId', $supplierIds);

            if (! empty($validated['supplierId'])) {
                $supplierIdQuery->where('SupplierId', (int)$validated['supplierId']);
            }

            $supplierId = $supplierIdQuery->value('SupplierId');

            if (! $supplierId) {
                return response()->json(['error' => 'No invitation found'], 403);
            }

            if (! empty($validated['rfqLineId'])) {
                $validLineForRfq = RFQLine::query()
                    ->where('RFQId', $validated['rfqId'])
                    ->where('Id', $validated['rfqLineId'])
                    ->exists();

                if (! $validLineForRfq) {
                    return response()->json(['error' => 'Invalid rfqLineId for this rfqId'], 422);
                }
            }

            $systemUserId = (int)($user->Id ?? 0);
            if ($systemUserId <= 0) {
                $systemUser = DB::table('t_Users')->select('Id')->first();
                $systemUserId = (int)($systemUser?->Id ?? 1);
                Log::warning('postClarification: missing user Id, using fallback system user', [
                    'thirdPartyId' => $user->ThirdPartyId ?? null,
                    'systemUserId' => $systemUserId,
                ]);
            }

            RFQClarification::create([
                'RFQId' => $validated['rfqId'],
                'SupplierId' => $supplierId,
                'RFQLineId' => $validated['rfqLineId'] ?? null,
                'Question' => $validated['question'],
                'CreatedBy' => $systemUserId,
                'ModifiedBy' => $systemUserId,
            ]);

            return response()->json(['message' => 'Clarification submitted'], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('postClarification failed', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function listClarifications(int|string $rfq): JsonResponse
    {
        try {
            $user = $this->thirdPartyUser();

            if (! $user || ! isset($user->ThirdPartyId)) {
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
                    'CreatedOn',
                ]);

            return response()->json(['data' => $data]);
        } catch (\Throwable $e) {
            Log::error('listClarifications failed', ['rfq' => $rfq, 'error' => $e->getMessage()]);

            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
