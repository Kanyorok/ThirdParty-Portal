<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\RFQEvaluation;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\Supplier;

class RFQEvaluationController extends Controller
{
    public function index()
    {
        // Fetch RFQ evaluations from the database
        $rfqEvaluations = RFQEvaluation::with(['rfq'])->get();

        // Return the view with the RFQ evaluations
        return view('procurement.rfqevaluation.index', compact('rfqEvaluations'));
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
        dd($supplierIds);
        // Fetch the suppliers based on the extracted IDs
        $suppliers = Supplier::whereIn('ContactEmail', $supplierIds)->get();     
        // Return the view with the form
        return view('procurement.rfqevaluation.create', compact('rfqs', 'suppliers', 'currencies'));
    }
}
