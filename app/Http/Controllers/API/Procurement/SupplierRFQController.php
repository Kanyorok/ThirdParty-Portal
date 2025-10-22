<?php

namespace App\Http\Controllers\API\Procurement;

use App\Helpers\SystemHelper;
use App\Http\Controllers\Controller;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQLine;
use App\Models\Procurement\RFQResponse;
use App\Models\Procurement\RFQResponseItem;
use App\Models\Procurement\RFQClarification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Core\Currency;

class SupplierRFQController extends Controller
{
    public function listInvitations(Request $request): JsonResponse
    {
        $user = Auth::user();
        $thirdPartyId = $user->ThirdPartyId ?? null;
        if (!$thirdPartyId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $mySupplierIds = DB::table('t_Suppliers')
            ->where('ThirdPartyID', $thirdPartyId)
            ->where('Active_Status', 1)
            ->pluck('Id');

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
    }

    public function getInvitation(int $rfqId): JsonResponse
    {
        $user = Auth::user();
        $thirdPartyId = $user->ThirdPartyId ?? null;
        if (!$thirdPartyId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $rfq = RFQ::find($rfqId);
        if (!$rfq) {
            return response()->json(['error' => 'RFQ not found'], 404);
        }

        // Find invited supplier rows for this third party for this RFQ
        $mySupplierIds = DB::table('t_Suppliers')->where('ThirdPartyID', $thirdPartyId)->where('Active_Status', 1)->pluck('Id');
        $invitation = DB::table('t_RFQ_Supplier')->where('RFQId', $rfqId)->whereIn('SupplierId', $mySupplierIds)->select('SupplierId', 'Status', 'CreatedOn', 'ModifiedOn')->first();
        if (!$invitation) {
            return response()->json(['error' => 'No invitation for this RFQ'], 404);
        }

        $rfqLines = RFQLine::with('uom')->where('RFQId', $rfqId)->get();
        $items = $rfqLines->map(function (RFQLine $line) {
            return [
                'id' => $line->Id,
                'itemName' => $line->ItemName,
                'quantity' => (float)$line->Quantity,
                'uomId' => $line->UOM,
                'uomName' => optional($line->uom)->Name,
                'itemCategoryId' => $line->ItemCategoryId,
            ];
        });

        $currencies = Currency::query()
            ->orderByRaw("CASE WHEN Symbol = 'Ksh' THEN 0 ELSE 1 END")
            ->orderBy('Name')
            ->get(['Id', 'Name', 'Code', 'Symbol']);

        // Existing response for this supplier?
        $existing = DB::table('t_RFQResponse')
            ->where('RFQId', $rfq->Id)
            ->whereIn('SupplierId', $mySupplierIds)
            ->whereNull('DeletedOn')
            ->orderByDesc('Id')
            ->first(['Currency', 'DurationDays', 'SubmittedOn', 'Status', 'Id', 'SupplierId']);

        $response = null;
        if ($existing) {
            $responseItems = DB::table('t_ResponseItems')
                ->where('RfqResponseId', $existing->Id)
                ->get(['RfqResponseId', 'UOM', 'ItemName', 'Quantity', 'QuotedPrice', 'TotalPayable']);
            $response = [
                'currency' => $existing->Currency,
                'durationDays' => (int)$existing->DurationDays,
                'submittedAt' => $existing->SubmittedOn ? \Illuminate\Support\Carbon::parse($existing->SubmittedOn)->toISOString() : null,
                'items' => $responseItems->map(fn($ri) => [
                    'rfqLineId' => null, // legacy does not track rfq line id here
                    'quotedPrice' => (float)$ri->QuotedPrice,
                    'totalPayable' => (float)$ri->TotalPayable,
                ]),
            ];
        }

        // Compute status and canRespond
        $closing = $rfq->SubmissionDeadline ? \Illuminate\Support\Carbon::parse($rfq->SubmissionDeadline) : null;
        $isClosed = $closing ? now()->greaterThan($closing) : false;
        $status = $isClosed ? 'CLOSED' : (($existing && strtoupper($existing->Status ?? '') === 'FINAL') ? 'SUBMITTED' : 'OPEN');
        $canRespond = !$isClosed && (!$existing || strtoupper($existing->Status ?? '') !== 'FINAL');

        return response()->json([
            'rfq' => [
                'id' => $rfq->Id,
                'number' => $rfq->RFQNumber,
                'comments' => $rfq->Comments,
                'status' => $rfq->Status,
                'submissionDeadline' => $rfq->SubmissionDeadline ? \Illuminate\Support\Carbon::parse($rfq->SubmissionDeadline)->toISOString() : null,
            ],
            'invitation' => $invitation,
            'items' => $items,
            'currencies' => $currencies->map(fn($c) => [
                'id' => $c->Id,
                'name' => $c->Name,
                'code' => $c->Code,
                'symbol' => $c->Symbol,
                'isDefault' => $c->Symbol === 'Ksh',
            ]),
            'response' => $response,
            'status' => $status,
            'canRespond' => $canRespond,
        ]);
    }

    public function submitResponse(Request $request): JsonResponse
    {
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

        $user = Auth::user();
        $thirdPartyId = $user->ThirdPartyId ?? null;
        if (!$thirdPartyId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $rfq = RFQ::find($request->rfqId);
        if (!$rfq) {
            return response()->json(['error' => 'RFQ not found'], 404);
        }

        // Match invitation SupplierId for this third party
        $mySupplierIds = DB::table('t_Suppliers')->where('ThirdPartyID', $thirdPartyId)->where('Active_Status', 1)->pluck('Id');
        $supplierId = DB::table('t_RFQ_Supplier')->where('RFQId', $rfq->Id)->whereIn('SupplierId', $mySupplierIds)->min('SupplierId');
        if (!$supplierId) {
            return response()->json(['error' => 'No invitation found for this supplier'], 403);
        }

        $tradingName = DB::table('t_ThirdParties as tp')->join('t_Suppliers as s', 's.ThirdPartyID', '=', 'tp.Id')->where('s.Id', $supplierId)->value('tp.TradingName');
        $actor = SystemHelper::user();

        $existing = RFQResponse::where('RFQId', $rfq->Id)->where('SupplierId', $supplierId)->whereNull('DeletedOn')->first();
        if ($existing && strtoupper($existing->Status ?? 'FINAL') === 'FINAL') {
            return response()->json([
                'code' => 'AlreadySubmitted',
                'message' => 'Response already submitted.',
                'response' => [
                    'currency' => $existing->Currency,
                    'durationDays' => (int)$existing->DurationDays,
                    'submittedAt' => optional($existing->SubmittedOn)->toISOString(),
                ],
            ], 409);
        }

        DB::transaction(function () use ($request, $rfq, $supplierId, $tradingName, $actor, $existing) {
            $prefix = 'RFQRE-';
            $last = RFQResponse::where('RFQResponseNumber', 'like', $prefix . '%')->orderBy('Id', 'desc')->first();
            $lastNumber = $last ? intval(substr($last->RFQResponseNumber, strlen($prefix))) : 0;
            $code = $existing?->RFQResponseNumber ?? ($prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT));

            $data = [
                'RFQId' => $rfq->Id,
                'RFQResponseNumber' => $code,
                'RFQNumber' => $rfq->RFQNumber,
                'SupplierId' => $supplierId,
                'SupplierName' => $tradingName,
                'TotalPayable' => collect($request->items)->sum('totalPayable'),
                'Currency' => $request->currency,
                'DurationDays' => $request->durationDays,
                'Status' => $request->boolean('isDraft') ? 'DRAFT' : 'FINAL',
                'SubmittedOn' => $request->boolean('isDraft') ? null : now(),
                'ModifiedBy' => $actor->Id,
            ];

            if ($existing) {
                $existing->update($data);
                $rfqResponse = $existing;
                // refresh items: simple approach - delete old and insert new
                DB::table('t_ResponseItems')->where('RfqResponseId', $existing->Id)->delete();
            } else {
                $data['CreatedBy'] = $actor->Id;
                $rfqResponse = RFQResponse::create($data);
            }

            foreach ($request->items as $item) {
                RFQResponseItem::create([
                    'RfqResponseId' => $rfqResponse->Id,
                    'ItemName' => DB::table('t_RFQLines')->where('Id', $item['rfqLineId'])->value('ItemName'),
                    'UOM' => DB::table('t_RFQLines')->where('Id', $item['rfqLineId'])->value('UOM'),
                    'Quantity' => DB::table('t_RFQLines')->where('Id', $item['rfqLineId'])->value('Quantity'),
                    'QuotedPrice' => $item['quotedPrice'],
                    'TotalPayable' => $item['totalPayable'],
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);
            }
        });

        return response()->json(['message' => 'Response submitted'], 201);
    }

    public function postClarification(Request $request): JsonResponse
    {
        $request->validate([
            'rfqId' => 'required|integer|exists:t_RFQ,Id',
            'question' => 'required|string',
            'rfqLineId' => 'nullable|integer|exists:t_RFQLines,Id',
        ]);

        $user = Auth::user();
        $thirdPartyId = $user->ThirdPartyId ?? null;
        if (!$thirdPartyId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $mySupplierIds = DB::table('t_Suppliers')->where('ThirdPartyID', $thirdPartyId)->where('Active_Status', 1)->pluck('Id');
        $supplierId = DB::table('t_RFQ_Supplier')->where('RFQId', $request->rfqId)->whereIn('SupplierId', $mySupplierIds)->min('SupplierId');
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

    public function listClarifications(int $rfqId): JsonResponse
    {
        $user = Auth::user();
        $thirdPartyId = $user->ThirdPartyId ?? null;
        if (!$thirdPartyId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $mySupplierIds = DB::table('t_Suppliers')->where('ThirdPartyID', $thirdPartyId)->pluck('Id');
        $clarifications = RFQClarification::query()
            ->where('RFQId', $rfqId)
            ->whereIn('SupplierId', $mySupplierIds)
            ->whereNull('DeletedOn')
            ->orderByDesc('Id')
            ->get(['Id', 'RFQId', 'SupplierId', 'RFQLineId', 'Question', 'Answer', 'CreatedOn']);

        return response()->json(['data' => $clarifications]);
    }
}
