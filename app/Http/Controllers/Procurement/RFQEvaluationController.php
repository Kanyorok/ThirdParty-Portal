<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\SupplierResponseEvaluation;
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
        // 1. Validate main fields
        $validated = $request->validate([
            'CommitteeMember' => 'required|string',
            'UserID' => 'required|string',
            'RFQId' => 'required|integer',
            'RFQComments' => 'nullable|string',
            'Confirmation' => 'required|boolean',
            'Evaluations' => 'required|array',
        ]);

        // 2. Create RFQ Evaluation
        $rfqEval = RFQEvaluation::create([
            'CommitteeMemberName' => $request->CommitteeMember,
            'UserCode' => $request->UserID,
            'RFQId' => $request->RFQId,
            'RFQComment' => $request->RFQComments,
            'Confirmation' => $request->Confirmation,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);

        // 3. Loop through supplier evaluations
        foreach ($request->Evaluations as $eval) {
            $evalModel = SupplierResponseEvaluation::create([
                'SupplierId' => $eval['SupplierId'],
                'TechnicalQuality' => $eval['TechnicalQuality'],
                'TechnicalQualityComments' => $eval['TechnicalQualityComments'],
                'Pricing' => $eval['Pricing'],
                'PricingComments' => $eval['PricingComments'],
                'DeliveryTime' => $eval['DeliveryTime'],
                'DeliveryTimeComments' => $eval['DeliveryTimeComments'],
                'PastExperience' => $eval['PastExperience'],
                'PastExperienceComments' => $eval['PastExperienceComments'],
                'CreatedBy' => auth()->user()->Id,
                'ModifiedBy' => auth()->user()->Id,
            ]);

            // Attach to pivot
            $rfqEval->evaluations()->attach($evalModel->id);
        }

        return redirect()->route('evaluations.index')->with('success', 'Evaluation submitted successfully.');
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
