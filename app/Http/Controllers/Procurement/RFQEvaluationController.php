<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
