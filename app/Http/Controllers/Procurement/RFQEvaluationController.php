<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQCommittee;
use App\Models\Procurement\RFQCommitteeMember;
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
            'sections.criteriaSettings', 'rfqResponses.supplier','committeeMembers.user.employee'
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
            ->with('supplier', 'items.uom')
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
    public function getCommitteeMemberInfo($rfqId)
    {
        $rfq = RFQ::find($rfqId);

        if (!$rfq) {
            return response()->json(['error' => 'RFQ not found'], 404);
        }

        $employeeId = auth()->user()?->employee?->Id;

        $member = RFQCommitteeMember::with('user.employee')
            ->where('RFQID', $rfq->Id)
            ->where('UserID', $employeeId)
            ->first();

        if (!$member) {
            return response()->json(['error' => 'User not part of committee']);
        }

        return response()->json([
            'CommitteeMember' => $member->user->Name,
            'UserID' => $member->UserID
        ]);
    }
}
