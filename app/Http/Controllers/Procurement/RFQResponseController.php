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
       // Fetch RFQ responses from the database
       $rfqResponses = RFQResponse::with(['rfq'])->get();

       // Return the view with the RFQ responses
       return view('procurement.rfqresponses.index', compact('rfqResponses'));
   }

    public function create()
    {
        // Fetch RFQs
        $rfqs = RFQ::all();
        $currencies = config('app.currencies'); 

        // Extract and decode the Suppliers field from each RFQ
        $supplierIds = $rfqs->pluck('Suppliers') // Get the Suppliers JSON field
                            ->filter() // Remove null values
                            ->flatMap(function ($suppliers) {
                                return json_decode($suppliers, true); // Decode JSON into an array
                            })
                            ->pluck('ContactEmail') // Extract SupplierName
                            ->unique(); // Ensure unique IDs

        // Fetch the suppliers based on the extracted IDs
        $suppliers = Supplier::whereIn('ContactEmail', $supplierIds)->get();     
        // Return the view with the form
        return view('procurement.rfqresponses.create', compact('rfqs', 'suppliers', 'currencies'));
    }
    /**
     * Store a newly created RFQ response in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
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
            'TotalPayable' => 'required|numeric|min:0', // Validate the aggregate total
        ]);

        // Generate a unique RFQResponseNumber
        $prefix = 'RFQRE-';
        $lastRFQResponse = RFQResponse::where('RFQResponseNumber', 'like', $prefix . '%')->orderBy('Id', 'desc')->first();
        $lastNumber = $lastRFQResponse ? intval(substr($lastRFQResponse->RFQResponseNumber, strlen($prefix))) : 0;
        $newRFQResponseNumber = $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
      
        // Create a new RFQ response
        RFQResponse::create([
            'RFQId' => $request->RFQId,
            'RFQResponseNumber' => $newRFQResponseNumber,
            'RFQNumber' => $request->RFQNumber,
            'SupplierName' => $request->SupplierName,
            'TotalPayable' => $request->TotalPayable, // Store the computed total
            'Currency' => $request->Currency,
            'DurationDays' => $request->DurationDays,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
            'RequisitionItems' => json_encode($request->RequisitionItems), // Store the items as JSON
        ]);

        // Redirect back with success message
        return redirect()->route('rfqresponses.index')->with('success', 'RFQ Response created successfully.');
    }

   public function show($id)
   {
       // Fetch the RFQ response by ID
       $rfqResponse = RFQResponse::with(['rfq'])->findOrFail($id);

       // Return the view with the RFQ response details
       return view('procurement.rfqresponses.show', compact('rfqResponse'));
   }
   public function edit($id)
   {
       // Fetch the RFQ response by ID
       $rfqResponse = RFQResponse::with(['rfq'])->findOrFail($id);

       // Fetch RFQs for the form
       $rfqs = RFQ::all();

       // Return the view with the form
       return view('procurement.rfq_responses.edit', compact('rfqResponse', 'rfqs'));
   }
   public function update(Request $request, $id)
   {
       // Validate the request data
       $request->validate([
           'RFQId' => 'required|exists:t_RFQ,Id',
           'RFQNumber' => 'required|string|max:255',
           'Quantity' => 'required|integer|min:1',
           'QuotedPrice' => 'required|numeric|min:0',
           'TotalPayable' => 'required|numeric|min:0',
           'Currency' => 'required|string|max:3',
           'DurationDays' => 'required|integer|min:1',
           'Description' => 'nullable|string|max:255',
           'SupplierName' => 'required|string|max:255',
       ]);

       // Fetch the RFQ response by ID
       $rfqResponse = RFQResponse::findOrFail($id);

       // Update the RFQ response
       $rfqResponse->update($request->all());

       // Redirect back with success message
       return redirect()->route('rfq_responses.index')->with('success', 'RFQ Response updated successfully.');
   }

    public function getRequisitionItems($rfqId)
    {
        // Fetch the RFQ by ID
        $rfq = RFQ::find($rfqId);

        // Check if the RFQ exists
        if (!$rfq) {
            return response()->json(['error' => 'RFQ not found'], 404);
        }

        // Check if the RequisitionItems field exists
        if (!$rfq->RequisitionItems) {
            return response()->json(['error' => 'No requisition items found'], 404);
        }

        // Decode the RequisitionItems JSON field only if it's a string
        $requisitionItems = is_string($rfq->RequisitionItems)
            ? json_decode($rfq->RequisitionItems, true)
            : $rfq->RequisitionItems;

        // Check if decoding was successful
        if (is_string($rfq->RequisitionItems) && json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON in RequisitionItems'], 500);
        }

        // Ensure quantity is cast to an integer
        $requisitionItems = collect($requisitionItems)->map(function ($item) {
            return [
                'name' => $item['name'],
                'description' => $item['description'],
                'quantity' => (int) $item['quantity'], // Cast quantity to integer
                'unit' => $item['unit'] ?? null, // Include unit if it exists
            ];
        });

        // Return the requisition items as JSON
        return response()->json([
            'requisitionItems' => $requisitionItems,
        ]);
    }

   public function destroy($id)
   {
       // Fetch the RFQ response by ID
       $rfqResponse = RFQResponse::findOrFail($id);

       // Delete the RFQ response
       $rfqResponse->delete();

       // Redirect back with success message
       return redirect()->route('rfqresponses.index')->with('success', 'RFQ Response deleted successfully.');
   }
}
