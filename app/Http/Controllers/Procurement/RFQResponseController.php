<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Core\Currency;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQLine;
use App\Models\Procurement\RFQResponse;
use App\Models\Procurement\RFQResponseItem;
use App\Models\ThirdParies\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RFQResponseController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', RFQResponse::class);
        $rfqResponses = RFQResponse::with(['rfq', 'items', 'items.uom'])->latest()->paginate(10);

        return view('procurement.rfqresponses.index', compact('rfqResponses'));
    }

    public function create()
    {
        $this->authorize('create', RFQResponse::class);

        // Include both Approved and Published RFQs
        // Filter out RFQs where Evaluation has started (exists in t_RFQEvaluations)
        $rfqs = RFQ::whereIn('Status', ['Ap', 'AP', 'Approved', 'Pub', 'Published'])
            ->where(function ($query) {
                $query->whereNull('SubmissionDeadline')
                      ->orWhere('SubmissionDeadline', '>=', now());
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('t_RFQEvaluations')
                      ->whereColumn('t_RFQEvaluations.RFQId', 't_RFQ.Id')
                      ->whereNull('t_RFQEvaluations.DeletedOn');
            })
            ->select('Id', 'RFQNumber', 'Comments', 'Status')
            ->get();

        // Further filter: Exclude RFQs where all invited suppliers have already responded
        $rfqs = $rfqs->filter(function ($rfq) {
            // Count invited suppliers
            $invitedCount = DB::table('t_RFQ_Supplier')->where('RFQId', $rfq->Id)->count();

            // If no private invitations, assumed open or handled differently.
            // If there ARE invitations, we check if everyone responded.
            if ($invitedCount > 0) {
                $responseCount = RFQResponse::where('RFQId', $rfq->Id)
                   ->whereNull('DeletedOn')
                   ->count();

                // If all invited (or more/equal) have responded, hide this RFQ
                if ($responseCount >= $invitedCount) {
                    return false;
                }
            }

            return true;
        });

        $currencies = Currency::query()
            ->orderByRaw("CASE WHEN Symbol = 'Ksh' THEN 0 ELSE 1 END")
            ->orderBy('Name')
            ->get(['Id', 'Name', 'Code', 'Symbol']);

        // Suppliers are loaded via AJAX based on RFQ selection, so pass empty list initially
        $suppliers = collect();

        return view('procurement.rfqresponses.create', compact('rfqs', 'suppliers', 'currencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'RFQId' => 'required|exists:t_RFQ,Id',
            'RFQNumber' => 'required|string|max:255',
            'SupplierId' => 'required|exists:t_Suppliers,Id',
            'Currency' => 'required|string|max:10',
            'DurationDays' => 'required|integer|min:1',
            'TotalPayable' => 'required|numeric|min:0',
            'RequisitionItems' => 'required|array|min:1',
            'RequisitionItems.*.name' => 'required|string|max:255',
            'RequisitionItems.*.uom_id' => 'required|integer',
            'RequisitionItems.*.quantity' => 'required|numeric|min:0',
            'RequisitionItems.*.quotedprice' => 'required|numeric|min:0',
            'RequisitionItems.*.totalpayable' => 'required|numeric|min:0',
        ]);

        // Check Submission Deadline
        $rfq = RFQ::find($request->RFQId);
        if ($rfq && $rfq->SubmissionDeadline && \Carbon\Carbon::parse($rfq->SubmissionDeadline)->isPast()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'The submission deadline for this RFQ has passed. Responses can no longer be submitted.');
        }

        $userId = Auth::user()->Id;

        // Get the selected supplier with relationships
        $supplier = Supplier::with('supplierMaster.thirdParty')->findOrFail($request->SupplierId);

        $supplierName = $supplier->supplierMaster->thirdParty->TradingName
            ?? $supplier->supplierMaster->thirdParty->ThirdPartyName;

        // Guard: prevent duplicate response for same RFQ + ThirdParty
        $supplierThirdPartyId = $supplier->supplierMaster->ThirdPartyId;
        $exists = RFQResponse::where('RFQId', $request->RFQId)
            ->whereIn('SupplierId', function ($q) use ($supplierThirdPartyId) {
                $q->select('s.Id')
                    ->from('t_Suppliers as s')
                    ->join('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
                    ->where('sm.ThirdPartyId', $supplierThirdPartyId)
                    ->whereNull('s.DeletedOn');
            })
            ->whereNull('DeletedOn')
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'A response from this supplier for this RFQ already exists.');
        }

        // Generate RFQ Response Number
        $prefix = 'RFQRE-';
        $lastRFQResponse = RFQResponse::where('RFQResponseNumber', 'like', $prefix . '%')
            ->orderBy('Id', 'desc')
            ->first();
        $lastNumber = $lastRFQResponse ? intval(substr($lastRFQResponse->RFQResponseNumber, strlen($prefix))) : 0;
        $newRFQResponseNumber = $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($request, $supplier, $supplierName, $newRFQResponseNumber, $userId) {
            $rfqResponse = RFQResponse::create([
                'RFQId' => $request->RFQId,
                'RFQResponseNumber' => $newRFQResponseNumber,
                'RFQNumber' => $request->RFQNumber,
                'SupplierId' => $supplier->Id,
                'SupplierName' => $supplierName,
                'TotalPayable' => $request->TotalPayable,
                'Currency' => $request->Currency,
                'DurationDays' => $request->DurationDays,
                'Status' => 'Submitted',
                'CreatedBy' => $userId,
                'ModifiedBy' => $userId,
            ]);

            foreach ($request->RequisitionItems as $item) {
                RFQResponseItem::create([
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
        $rfqResponse = RFQResponse::with(['rfq', 'items'])->findOrFail($id);

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
            'RequisitionItems.*.id' => 'required|integer|exists:t_ResponseItems,Id',
            'RequisitionItems.*.quotedprice' => 'required|numeric|min:0',
            'RequisitionItems.*.totalpayable' => 'required|numeric|min:0',
        ]);

        $rfqResponse = RFQResponse::findOrFail($id);

        DB::transaction(function () use ($request, $rfqResponse) {
            $rfqResponse->update([
                'TotalPayable' => $request->TotalPayable,
                'DurationDays' => $request->DurationDays,
                'ModifiedBy' => Auth::id(),
            ]);

            foreach ($request->RequisitionItems as $itemData) {
                $item = RFQResponseItem::findOrFail($itemData['id']);
                $item->update([
                    'QuotedPrice' => $itemData['quotedprice'],
                    'TotalPayable' => $itemData['totalpayable'],
                    'ModifiedBy' => Auth::id(),
                ]);
            }
        });

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
        $rfqLines = RFQLine::with('uom')->where('RFQId', $rfqId)->get();

        if ($rfqLines->isEmpty()) {
            return response()->json(['error' => 'No RFQ lines found'], 404);
        }

        $items = $rfqLines->map(fn ($line) => [
            'id' => $line->Id,
            'ItemName' => $line->ItemName,
            'Quantity' => $line->Quantity,
            'UOM' => $line->UOM,
            'UOMName' => $line->uom->Name ?? 'N/A',
        ]);

        return response()->json(['requisitionItems' => $items]);
    }

    public function getSuppliers($rfqId)
    {
        $hasInvitationTable = Schema::hasTable('t_RFQ_Supplier');

        $query = DB::table('t_Suppliers as s')
            ->join('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
            ->join('t_ThirdParties as tp', 'tp.Id', '=', 'sm.ThirdPartyId')
            ->whereNull('s.DeletedOn')
            ->where('s.Active_Status', 1);

        if ($hasInvitationTable) {
            $query->join('t_RFQ_Supplier as p', 'p.SupplierId', '=', 's.Id')
                  ->where('p.RFQId', $rfqId);
        }

        // Filter out suppliers who have already responded to this RFQ
        $query->whereNotExists(function ($q) use ($rfqId) {
            $q->select(DB::raw(1))
              ->from('t_RFQResponse')
              ->whereColumn('t_RFQResponse.SupplierId', 's.Id')
              ->where('t_RFQResponse.RFQId', $rfqId)
              ->whereNull('t_RFQResponse.DeletedOn');
        });

        $suppliers = $query->select(
            's.Id',
            DB::raw("COALESCE(tp.TradingName, tp.ThirdPartyName) as SupplierName")
        )
            ->get();

        return response()->json($suppliers);
    }

    public function findExisting(Request $request)
    {
        $rfqId = $request->query('rfqId');
        $supplierId = $request->query('supplierId');

        if (! $rfqId || ! $supplierId) {
            return response()->json(['exists' => false]);
        }

        $thirdPartyId = DB::table('t_Suppliers as s')
            ->join('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
            ->where('s.Id', $supplierId)
            ->value('sm.ThirdPartyId');

        if (! $thirdPartyId) {
            return response()->json(['exists' => false]);
        }

        $existing = RFQResponse::with('items')
            ->where('RFQId', $rfqId)
            ->whereIn('SupplierId', function ($q) use ($thirdPartyId) {
                $q->select('s.Id')
                    ->from('t_Suppliers as s')
                    ->join('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
                    ->where('sm.ThirdPartyId', $thirdPartyId);
            })
            ->whereNull('DeletedOn')
            ->latest('Id')
            ->first();

        if (! $existing) {
            return response()->json(['exists' => false]);
        }

        return response()->json([
            'exists' => true,
            'header' => [
                'currency' => $existing->Currency,
                'durationDays' => (int)$existing->DurationDays,
                'status' => $existing->Status,
                'submittedOn' => $existing->created_at ? $existing->created_at->toISOString() : null,
            ],
            'items' => $existing->items->map(fn ($it) => [
                'name' => $it->ItemName,
                'uom' => $it->UOM,
                'quantity' => (float)$it->Quantity,
                'quotedPrice' => (float)$it->QuotedPrice,
                'totalPayable' => (float)$it->TotalPayable,
            ]),
        ]);
    }
}
