<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\RFQResponse;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\Supplier;

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

   public function store(Request $request)
   {

       // Validate the request data
       $request->validate([
           'RFQId' => 'required|exists:t_RFQ,Id',
           'RequisitionItems' => 'nullable|array',
           'RFQNumber' => 'required|string|max:255',
           'SupplierName' => 'required|string|max:255',
           'TotalPayable' => 'required|numeric|min:0',
           'Currency' => 'required|string|max:3',
           'DurationDays' => 'required|integer|min:1',
           'RFQResponseNumber' => 'required|string|max:255',
       ]);

       // Create a new RFQ response
       RFQResponse::create($request->all());

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
           'RFQResponseNumber' => 'required|string|max:255',
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
