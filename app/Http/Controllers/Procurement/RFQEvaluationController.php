<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Core\Currency;
use App\Models\Procurement\RFQEvaluation;
use App\Models\Procurement\RFQResponse;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\RFQ;

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
        $rfqs = RFQ::whereHas('rfqResponses')->get();
        
        $currencies = config('app.currencies'); 

        $rfqresponses = RFQResponse::all();

        // Return the view with the form
        return view('procurement.rfqevaluation.create', compact('rfqs', 'currencies'));
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'RFQId' => 'required|exists:t_RFQs,id',
            'SupplierId' => 'required|exists:t_Suppliers,id',
            'PricingScore' => 'required|numeric|min:0|max:100',
            'DeliveryTimeScore' => 'required|numeric|min:0|max:100',
            'PastExperienceScore' => 'required|numeric|min:0|max:100',
            'TechnicalQualityScore' => 'required|numeric|min:0|max:100',
            'CommitteeMemberName' => 'required|string|max:255',
            'Comments' => 'nullable|string|max:255',
        ]);

        // Create a new RFQ evaluation
        RFQEvaluation::create([
            'RFQId' => $request->RFQId,
            'SupplierId' => $request->SupplierId,
            'EvaluationScore' => $request->EvaluationScore,
            'Comments' => $request->Comments,
        ]);

        // Redirect back with success message
        return redirect()->route('rfqevaluation.index')->with('success', 'RFQ Evaluation created successfully.');
    }

    public function getRFQResponses($rfqId)
    {
        // Fetch RFQ responses where RFQId matches the selected RFQ
        $rfqResponses = RFQResponse::where('RFQId', $rfqId)
            ->with('supplier') // Assuming you have a relationship with the Supplier model
            ->get();

        // Return the responses as JSON
        return response()->json($rfqResponses);
    }
}
