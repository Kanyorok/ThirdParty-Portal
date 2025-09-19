<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\BidSubmission;
use App\Models\Procurement\Tender;
use App\Enums\Core\PermissionEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BidEvaluationController extends Controller
{
    /**
     * Display the bid evaluation interface
     */
    public function index(Request $request)
    {
        $this->authorize(PermissionEnum::BidSubmissionRead);
        
        // Get tenders with responsive bids ready for evaluation
        $tenders = Tender::whereHas('submissions', function($query) {
                $query->where('BidStatus', 'responsive');
            })
            ->select('Id', 'TenderNo', 'Title', 'OpeningDate', 'SubmissionDeadline')
            ->orderBy('OpeningDate', 'desc')
            ->get();

        $selectedTender = null;
        $responsiveBids = collect();
        $evaluationSummary = null;

        if ($request->has('tender')) {
            $selectedTender = Tender::where('TenderNo', $request->tender)->first();
            if ($selectedTender) {
                $responsiveBids = BidSubmission::forTender($selectedTender->TenderNo)
                    ->with(['supplier.thirdParty'])
                    ->whereIn('BidStatus', ['responsive', 'evaluated', 'awarded'])
                    ->where('IsResponsive', true)
                    ->orderBy('BidAmount')
                    ->get();
                
                $evaluationSummary = $this->getEvaluationSummary($responsiveBids);
            }
        }

        return view('procurement.tendering.bidopeningandevaluation.evaluation.index', 
            compact('tenders', 'selectedTender', 'responsiveBids', 'evaluationSummary'));
    }

    /**
     * Update bid evaluation scores
     */
    public function updateScores(Request $request, $bidId)
    {
        $this->authorize(PermissionEnum::BidSubmissionWrite);
        
        $request->validate([
            'technical_score' => 'required|numeric|min:0|max:100',
            'financial_score' => 'required|numeric|min:0|max:100',
            'evaluation_notes' => 'nullable|string|max:2000'
        ]);

        $bid = BidSubmission::findOrFail($bidId);

        if (!$bid->isResponsive()) {
            return redirect()->back()->with('error', 'Only responsive bids can be evaluated.');
        }

        DB::beginTransaction();

        try {
            $technicalScore = $request->technical_score;
            $financialScore = $request->financial_score;
            $evaluationNotes = $request->evaluation_notes;

            // Update bid scores
            $bid->updateScores($technicalScore, $financialScore, $evaluationNotes);

            // Log evaluation activity
            activity()
                ->performedOn($bid)
                ->causedBy(Auth::user())
                ->withProperties([
                    'action' => 'bid_evaluated',
                    'technical_score' => $technicalScore,
                    'financial_score' => $financialScore,
                    'total_score' => $technicalScore + $financialScore
                ])
                ->log("Bid evaluated: {$bid->SupplierName} - Total Score: " . ($technicalScore + $financialScore));

            DB::commit();

            return redirect()->back()->with('success', 
                "Evaluation completed for {$bid->SupplierName}. Total Score: " . ($technicalScore + $financialScore));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update evaluation: ' . $e->getMessage());
        }
    }

    /**
     * Get evaluation summary for a set of bids
     */
    private function getEvaluationSummary($bids)
    {
        $evaluatedBids = $bids->where('BidStatus', 'evaluated');
        $awardedBid = $bids->where('BidStatus', 'awarded')->first();
        
        return [
            'total_responsive' => $bids->count(),
            'pending_evaluation' => $bids->where('BidStatus', 'responsive')->count(),
            'evaluated' => $evaluatedBids->count(),
            'average_score' => $evaluatedBids->avg('TotalScore'),
            'highest_score' => $evaluatedBids->max('TotalScore'),
            'lowest_score' => $evaluatedBids->min('TotalScore'),
            'awarded_bid' => $awardedBid,
            'can_award' => $evaluatedBids->isNotEmpty() && !$awardedBid,
            'top_ranked' => $evaluatedBids->sortByDesc('TotalScore')->first()
        ];
    }
}