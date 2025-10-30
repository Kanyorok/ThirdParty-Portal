<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQLine;
use App\Models\Procurement\RFQResponse;
use App\Models\procurement\RFQResponseItem;
use App\Models\ThirdParies\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RFQResponseController extends Controller
{
    public function index()
    {
        $rfqResponses = RFQResponse::with(['rfq', 'items', 'items.uom'])->latest()->paginate(10);
        return view('procurement.rfqresponses.index', compact('rfqResponses'));
    }
    public function create()
    {
        $rfqs = RFQ::where('Status', 'Approved')->get();
        // Load currencies from DB, prioritize Ksh first
        $currencies = \App\Models\Core\Currency::query()
            ->orderByRaw("CASE WHEN Symbol = 'Ksh' THEN 0 ELSE 1 END")
            ->orderBy('Name')
            ->get(['Id', 'Name', 'Code', 'Symbol']);

        // Get all unique SupplierIds from the pivot table t_RFQ_Supplier
        $supplierIds = DB::table('t_RFQ_Supplier')->pluck('SupplierId')->unique();

        // Fetch the suppliers using those IDs
        $suppliers = Supplier::whereIn('Id', $supplierIds)->get();

        return view('procurement.rfqresponses.create', compact('rfqs', 'suppliers', 'currencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'RFQId' => 'required|exists:t_RFQ,Id',
            'RFQNumber' => 'required|string|max:255',
            'RequisitionItems' => 'required|array|min:1',
            'RequisitionItems.*.name' => 'required|string|max:255',
            'RequisitionItems.*.uom_id' => 'required|integer|exists:t_UOM,Id',
            'RequisitionItems.*.quantity' => 'required|integer|min:1',
            'RequisitionItems.*.quotedprice' => 'required|numeric|min:0',
            'RequisitionItems.*.totalpayable' => 'required|numeric|min:0',
            'SupplierId' => 'required|exists:t_Suppliers,Id',
            'Currency' => 'required|string|max:3',
            'DurationDays' => 'required|integer|min:1',
            'TotalPayable' => 'required|numeric|min:0',
        ]);

        $userId = \Illuminate\Support\Facades\Auth::user()->Id;
        $supplier = Supplier::findOrFail($request->SupplierId);
        $supplierName = DB::table('t_Suppliers as s')
            ->join('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyID')
            ->where('s.Id', $request->SupplierId)
            ->whereNull('s.DeletedOn')
            ->whereNull('tp.DeletedOn')
            ->value('tp.TradingName');

        $prefix = 'RFQRE-';
        $lastRFQResponse = RFQResponse::where('RFQResponseNumber', 'like', $prefix . '%')->orderBy('Id', 'desc')->first();
        $lastNumber = $lastRFQResponse ? intval(substr($lastRFQResponse->RFQResponseNumber, strlen($prefix))) : 0;
        $newRFQResponseNumber = $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

        // Guard: prevent duplicate response for same RFQ + ThirdParty (across any SupplierId rows)
        $supplierThirdPartyId = DB::table('t_Suppliers')->where('Id', $supplier->Id)->value('ThirdPartyID');
        $exists = RFQResponse::where('RFQId', $request->RFQId)
            ->whereIn('SupplierId', function ($q) use ($supplierThirdPartyId) {
                $q->select('Id')->from('t_Suppliers')->where('ThirdPartyID', $supplierThirdPartyId)->whereNull('DeletedOn');
            })
            ->whereNull('DeletedOn')
            ->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'A response from this supplier for the selected RFQ already exists.');
        }

        DB::transaction(function () use ($request, $supplier, $newRFQResponseNumber, $userId, $supplierName) {
            // Create RFQ Response (header)
            $rfqResponse = RFQResponse::create([
                'RFQId' => $request->RFQId,
                'RFQResponseNumber' => $newRFQResponseNumber,
                'RFQNumber' => $request->RFQNumber,
                'SupplierId' => $supplier->Id,
                'SupplierName' => $supplierName ?? $supplier->SupplierName,
                'TotalPayable' => $request->TotalPayable,
                'Currency' => $request->Currency, // should be value from t_Currencies.Code
                'DurationDays' => $request->DurationDays,
                'CreatedBy' => $userId,
                'ModifiedBy' => $userId,
            ]);

            // Create associated line items
            foreach ($request->RequisitionItems as $item) {
                $rfqResponse->items()->create([
                    'RfqResponseId' => $rfqResponse->Id,
                    'ItemName' => $item['name'],
                    'UOM' => $item['uom_id'],
                    'Quantity' => $item['quantity'],
                    'QuotedPrice' => $item['quotedprice'],
                    'TotalPayable' => $item['totalpayable'],
                    'CreatedBy' => $userId,
                    'ModifiedBy' => $userId,
                ]);
            }
        });

        return redirect()->route('rfqresponses.index')->with('success', 'RFQ Response created successfully.');
    }

    public function show($id)
    {
        $rfqResponse = RFQResponse::with(['rfq'])->findOrFail($id);
        return view('procurement.rfqresponses.show', compact('rfqResponse'));
    }

    public function edit($id)
    {
        $rfqResponse = RFQResponse::with(['rfq', 'items'])->findOrFail($id);
        $rfqs = RFQ::all();
        return view('procurement.rfqresponses.edit', compact('rfqResponse', 'rfqs'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'TotalPayable' => 'required|numeric|min:0',
            'DurationDays' => 'required|integer|min:1',
            'RequisitionItems' => 'required|array',
            'RequisitionItems.*.id' => 'nullable|integer|exists:t_ResponseItems,Id',
            'RequisitionItems.*.quotedprice' => 'required|numeric|min:0',
            'RequisitionItems.*.totalpayable' => 'required|numeric|min:0',
        ]);

        $rfqResponse = RFQResponse::findOrFail($id);

        // Only update editable fields
        $rfqResponse->update([
            'TotalPayable' => $request->TotalPayable,
            'DurationDays' => $request->DurationDays,
            'ModifiedBy' => \Illuminate\Support\Facades\Auth::id(),
        ]);

        // Only update editable fields in items
        foreach ($request->RequisitionItems as $itemData) {
            if (!empty($itemData['id'])) {
                $item = RFQResponseItem::findOrFail($itemData['id']);
                $item->update([
                    'QuotedPrice' => $itemData['quotedprice'],
                    'TotalPayable' => $itemData['totalpayable'],
                    'ModifiedBy' => \Illuminate\Support\Facades\Auth::id(),
                ]);
            }
        }

        return redirect()->route('rfqresponses.index')->with('success', 'RFQ Response updated successfully.');
    }

    public function destroy($id)
    {
        $rfqResponse = RFQResponse::findOrFail($id);
        $rfqResponse->delete();
        return redirect()->route('rfqresponses.index')->with('success', 'RFQ Response deleted successfully.');
    }

    public function getRequisitionItems($rfqId)
    {
        // Fetch RFQ lines and eager load uomDetails relation
        $rfqLines = RFQLine::with('uom')->where('RFQId', $rfqId)->get();

        if ($rfqLines->isEmpty()) {
            return response()->json(['error' => 'No RFQ lines found for the given RFQ ID'], 404);
        }

        // Transform for frontend
        $items = $rfqLines->map(function ($line) {
            return [
                'id' => $line->Id,
                'ItemName' => $line->ItemName,
                'Quantity' => $line->Quantity,
                'UOM' => $line->UOM,
                'UOMName' => $line->uom->Name ?? 'N/A',
            ];
        });

        return response()->json([
            'requisitionItems' => $items,
        ]);
    }


    public function getSuppliers($rfqId)
    {
        // Compute suppliers relevant to this RFQ by mapping RFQ line item categories
        $rfq = RFQ::with('rfqLines')->find($rfqId);
        if (!$rfq) {
            return response()->json([], 404);
        }

        // Categories used in this RFQ (from its lines)
        $itemCategoryIds = $rfq->rfqLines->pluck('ItemCategoryId')->unique()->filter()->values();

        // Expand to include ancestors and all descendants
        $allCategoryIds = collect();
        foreach ($itemCategoryIds as $catId) {
            $catId = (int)$catId;
            if (!$catId) {
                continue;
            }
            // climb ancestors
            $current = $catId;
            while ($current) {
                $allCategoryIds->push($current);
                $parent = DB::table('t_ItemCategories')->where('Id', $current)->value('ParentId');
                if ($parent === null || (int)$parent === 0) {
                    break;
                }
                $current = (int)$parent;
            }
        }
        // BFS descendants
        $queue = collect($allCategoryIds->unique()->values());
        while ($queue->isNotEmpty()) {
            $batch = $queue->splice(0, 200)->all();
            $children = DB::table('t_ItemCategories')->whereIn('ParentId', $batch)->pluck('Id');
            $new = $children->diff($allCategoryIds);
            if ($new->isNotEmpty()) {
                $allCategoryIds = $allCategoryIds->merge($new);
                $queue = $queue->merge($new);
            }
        }
        $allCategoryIds = $allCategoryIds->unique()->values();

        // Exclude suppliers (by ThirdParty) that already submitted FINAL responses to this RFQ
        $respondedThirdPartyIds = DB::table('t_RFQResponse as rr')
            ->join('t_Suppliers as rs', 'rs.Id', '=', 'rr.SupplierId')
            ->where('rr.RFQId', $rfqId)
            ->whereNull('rr.DeletedOn')
            ->whereNull('rs.DeletedOn')
            ->where('rr.Status', 'FINAL')
            ->pluck('rs.ThirdPartyID');

        // Prefer invited suppliers from pivot table t_RFQ_Supplier, then exclude those already with FINAL responses
        $suppliers = DB::table('t_RFQ_Supplier as p')
            ->join('t_Suppliers as s', 's.Id', '=', 'p.SupplierId')
            ->join('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyID')
            ->where('p.RFQId', $rfqId)
            ->whereNull('s.DeletedOn')
            ->whereNull('tp.DeletedOn')
            ->where('s.Active_Status', 1)
            ->whereNotIn('tp.Id', $respondedThirdPartyIds)
            ->groupBy('tp.Id', 'tp.TradingName')
            ->select(
                DB::raw('MIN(s.Id) as Id'),
                'tp.TradingName as SupplierName'
            )
            ->get();

        return response()->json($suppliers);
    }

    public function findExisting(Request $request)
    {
        $rfqId = (int)$request->query('rfqId');
        $supplierId = (int)$request->query('supplierId');

        if (!$rfqId || !$supplierId) {
            return response()->json(['exists' => false]);
        }

        // Find ThirdParty of the selected supplier
        $thirdPartyId = DB::table('t_Suppliers')->where('Id', $supplierId)->value('ThirdPartyID');
        if (!$thirdPartyId) {
            return response()->json(['exists' => false]);
        }

        $existing = RFQResponse::with('items')
            ->where('RFQId', $rfqId)
            ->whereIn('SupplierId', function ($q) use ($thirdPartyId) {
                $q->select('Id')->from('t_Suppliers')->where('ThirdPartyID', $thirdPartyId)->whereNull('DeletedOn');
            })
            ->whereNull('DeletedOn')
            ->orderByDesc('Id')
            ->first();

        if (!$existing) {
            return response()->json(['exists' => false]);
        }

        return response()->json([
            'exists' => true,
            'header' => [
                'currency' => $existing->Currency,
                'durationDays' => (int)($existing->DurationDays ?? 0),
                'status' => $existing->Status ?? null,
                'submittedOn' => $existing->SubmittedOn ? \Illuminate\Support\Carbon::parse($existing->SubmittedOn)->toISOString() : null,
            ],
            'items' => $existing->items->map(function ($it) {
                return [
                    'name' => $it->ItemName,
                    'uom' => $it->UOM,
                    'quantity' => (float)$it->Quantity,
                    'quotedPrice' => (float)$it->QuotedPrice,
                    'totalPayable' => (float)$it->TotalPayable,
                ];
            }),
        ]);
    }

    public function getRFQResponses($rfqId)
    {
        $responses = RFQResponse::with('supplier')
            ->where('RFQId', $rfqId)
            ->get(['SupplierId', 'SupplierName', 'TotalPayable', 'DurationDays']);

        return response()->json($responses);
    }

}
