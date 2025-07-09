<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQCriteria;
use App\Models\Procurement\RFQEvaluation;
use App\Models\Procurement\RFQResponse;
use App\Models\Procurement\SupplierResponseEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RFQEvaluationController extends Controller
{
    public function index()
    {
        // Fetch RFQ evaluations from the database
        $rfqEvaluations = RFQEvaluation::with(['rfq', 'evaluations'])->get();
        // dd($rfqEvaluations);
        // Return the view with the RFQ evaluations
        return view('procurement.rfqevaluation.index', compact('rfqEvaluations'));
    }

    public function create()
    {
        // Load RFQs with sections and criteria
        $rfqs = RFQ::with([
            'sections.criteriaSettings', // assuming this is how RFQ links to criteria
            'rfqResponses.supplier'
        ])->whereHas('rfqResponses')->get();

        $currencies = config('app.currencies');

        return view('procurement.rfqevaluation.create', compact('rfqs', 'currencies'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'CommitteeMember' => 'required|string',
            'UserID' => 'required|string',
            'RFQId' => 'required|integer|exists:t_RFQ,Id',
            'RFQComments' => 'nullable|string',
            'Confirmation' => 'required|boolean',
            'Evaluations' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            // Create main RFQ Evaluation record
            $rfqEval = RFQEvaluation::create([
                'CommitteeMemberName' => $request->CommitteeMember,
                'UserCode' => $request->UserID,
                'RFQId' => $request->RFQId,
                'RFQComment' => $request->RFQComments,
                'Confirmation' => $request->Confirmation,
                'CreatedBy' => auth()->id(),
                'ModifiedBy' => auth()->id(),
            ]);

            foreach ($request->Evaluations as $supplierId => $criteriaSet) {
                foreach ($criteriaSet as $criteriaId => $scoreData) {
                    SupplierResponseEvaluation::create([
                        'RFQEvaluationId' => $rfqEval->id,
                        'SupplierId' => $supplierId,
                        'CriteriaId' => $criteriaId,
                        'Score' => $scoreData['Score'],
                        'Comments' => $scoreData['Comments'] ?? null,
                        'CreatedBy' => auth()->id(),
                        'ModifiedBy' => auth()->id(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('evaluations.index')->with('success', 'Evaluation submitted successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', 'Error saving evaluation: ' . $th->getMessage());
        }
    }


    public function getRFQResponses($rfqId)
    {
        $rfqResponses = RFQResponse::where('RFQId', $rfqId)
            ->with('supplier')
            ->get();

        // Fetch criteria by section
        $criteria = RFQCriteria::with('criteria', 'section')
            ->where('RFQID', $rfqId)
            ->get()
            ->groupBy('SectionID');

        return response()->json([
            'responses' => $rfqResponses,
            'criteria' => $criteria
        ]);
    }
}
