<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQLine;
use App\Models\Procurement\RFQResponse;
use App\Models\ThirdParies\Supplier;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'RequisitionItems.*.uom' => 'nullable|string|max:50',
            'RequisitionItems.*.quantity' => 'required|integer|min:1',
            'RequisitionItems.*.quotedprice' => 'required|numeric|min:0',
            'RequisitionItems.*.totalpayable' => 'required|numeric|min:0',
            'SupplierId' => 'required|exists:t_Suppliers,Id',
            'Currency' => 'required|string|max:3',
            'DurationDays' => 'required|integer|min:1',
            'TotalPayable' => 'required|numeric|min:0',
        ]);

        $userId = auth()->user()->Id;
        $supplier = Supplier::findOrFail($request->SupplierId);

        $prefix = 'RFQRE-';
        $lastRFQResponse = RFQResponse::where('RFQResponseNumber', 'like', $prefix . '%')->orderBy('Id', 'desc')->first();
        $lastNumber = $lastRFQResponse ? intval(substr($lastRFQResponse->RFQResponseNumber, strlen($prefix))) : 0;
        $newRFQResponseNumber = $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($request, $supplier, $newRFQResponseNumber, $userId) {
            // Create RFQ Response (header)
            $rfqResponse = RFQResponse::create([
                'RFQId' => $request->RFQId,
                'RFQResponseNumber' => $newRFQResponseNumber,
                'RFQNumber' => $request->RFQNumber,
                'SupplierId' => $supplier->Id,
                'SupplierName' => $supplier->SupplierName,
                'TotalPayable' => $request->TotalPayable,
                'Currency' => $request->Currency,
                'DurationDays' => $request->DurationDays,
                'CreatedBy' => $userId,
                'ModifiedBy' => $userId,
            ]);

            // Create associated line items
            foreach ($request->RequisitionItems as $item) {
                $rfqResponse->items()->create([
                    'RfqResponseId' => $rfqResponse->Id,
                    'ItemName' => $item['name'],
                    'UOM' => $item['uom'] ?? null,
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
        // Fetch all RFQLines where RFQId matches the given $rfqId
        $rfqLines = RFQLine::where('RFQId', $rfqId)->get();
    
        if ($rfqLines->isEmpty()) {
            return response()->json(['error' => 'No RFQ lines found for the given RFQ ID'], 404);
        }
       
        // Return the RFQLines directly as JSON
        return response()->json([
            'requisitionItems' => $rfqLines,
        ]);
    }

    public function getSuppliers($rfqId)
    {
        // Assuming each RFQ has a relation to suppliers (many-to-many or hasMany)
        $rfq = RFQ::with('suppliers')->find($rfqId);

        if (!$rfq) {
            return response()->json([], 404);
        }
        
        // Return only necessary fields to keep the response lightweight
        return response()->json($rfq->suppliers->map(function ($supplier) {
            return [
                'Id' => $supplier->Id,
                'SupplierName' => $supplier->SupplierName,
            ];
        }));
    }

    public function getRFQResponses($rfqId)
    {
        $responses = RFQResponse::with('supplier')
            ->where('RFQId', $rfqId)
            ->get(['SupplierId', 'SupplierName', 'TotalPayable', 'DurationDays']);

        return response()->json($responses);
    }

}
