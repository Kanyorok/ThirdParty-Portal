<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\RFQResponse;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\Supplier;
use Illuminate\Support\Facades\Auth;

class RFQResponseController extends Controller
{
    public function index()
    {
        $rfqResponses = RFQResponse::with(['rfq'])->get();
        return view('procurement.rfqresponses.index', compact('rfqResponses'));
    }

    public function create()
    {
        $rfqs = RFQ::all();
        $currencies = config('app.currencies');

        $supplierIds = $rfqs->pluck('Suppliers')
            ->filter()
            ->flatMap(function ($suppliers) {
                return json_decode($suppliers, true);
            })
            ->pluck('ContactEmail')
            ->unique();

        $suppliers = Supplier::whereIn('ContactEmail', $supplierIds)->get();
        return view('procurement.rfqresponses.create', compact('rfqs', 'suppliers', 'currencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'RFQId' => 'required|exists:t_RFQ,Id',
            'RFQNumber' => 'required|string|max:255',
            'RequisitionItems' => 'required|array',
            'RequisitionItems.*.name' => 'required|string|max:255',
            'RequisitionItems.*.quantity' => 'required|integer|min:1',
            'RequisitionItems.*.quotedprice' => 'required|numeric|min:0',
            'RequisitionItems.*.description' => 'required|string|max:255',
            'RequisitionItems.*.totalpayable' => 'required|numeric|min:0',
            'SupplierName' => 'required|string|max:255',
            'Currency' => 'required|string|max:3',
            'DurationDays' => 'required|integer|min:1',
            'TotalPayable' => 'required|numeric|min:0',
        ]);

        $prefix = 'RFQRE-';
        $lastRFQResponse = RFQResponse::where('RFQResponseNumber', 'like', $prefix . '%')->orderBy('Id', 'desc')->first();
        $lastNumber = $lastRFQResponse ? intval(substr($lastRFQResponse->RFQResponseNumber, strlen($prefix))) : 0;
        $newRFQResponseNumber = $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

        RFQResponse::create([
            'RFQId' => $request->RFQId,
            'RFQResponseNumber' => $newRFQResponseNumber,
            'RFQNumber' => $request->RFQNumber,
            'SupplierName' => $request->SupplierName,
            'TotalPayable' => $request->TotalPayable,
            'Currency' => $request->Currency,
            'DurationDays' => $request->DurationDays,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
            'RequisitionItems' => json_encode($request->RequisitionItems),
        ]);

        return redirect()->route('rfqresponses.index')->with('success', 'RFQ Response created successfully.');
    }

    public function show($id)
    {
        $rfqResponse = RFQResponse::with(['rfq'])->findOrFail($id);
        return view('procurement.rfqresponses.show', compact('rfqResponse'));
    }

    public function edit($id)
    {
        $rfqResponse = RFQResponse::with(['rfq'])->findOrFail($id);
        $rfqs = RFQ::all();
        return view('procurement.rfqresponses.edit', compact('rfqResponse', 'rfqs'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'RFQId' => 'required|exists:t_RFQ,Id',
            'RFQNumber' => 'required|string|max:255',
            'SupplierName' => 'required|string|max:255',
            'TotalPayable' => 'required|numeric|min:0',
            'Currency' => 'required|string|max:3',
            'DurationDays' => 'required|integer|min:1',
        ]);

        $rfqResponse = RFQResponse::findOrFail($id);
        $rfqResponse->update($request->only([
            'RFQId', 'RFQNumber', 'SupplierName', 'TotalPayable', 'Currency', 'DurationDays'
        ]));

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
        $rfq = RFQ::find($rfqId);

        if (!$rfq) {
            return response()->json(['error' => 'RFQ not found'], 404);
        }

        if (!$rfq->RequisitionItems) {
            return response()->json(['error' => 'No requisition items found'], 404);
        }

        $requisitionItems = is_string($rfq->RequisitionItems)
            ? json_decode($rfq->RequisitionItems, true)
            : $rfq->RequisitionItems;

        if (is_string($rfq->RequisitionItems) && json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON in RequisitionItems'], 500);
        }

        $requisitionItems = collect($requisitionItems)->map(function ($item) {
            return [
                'name' => $item['name'],
                'description' => $item['description'],
                'quantity' => (int) $item['quantity'],
                'unit' => $item['unit'] ?? null,
            ];
        });

        return response()->json([
            'requisitionItems' => $requisitionItems,
        ]);
    }

    public function getSuppliersByRFQ($id)
    {
        try {
            $rfq = RFQ::with('suppliers')->findOrFail($id);
            $suppliers = $rfq->suppliers->map(function ($supplier) {
                return [
                    'SupplierName' => $supplier->SupplierName,
                    'ContactEmail' => $supplier->ContactEmail,
                ];
            });
            return response()->json($suppliers);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load suppliers again'], 500);
        }
    }

}
