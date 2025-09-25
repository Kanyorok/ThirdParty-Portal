<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\TenderCommitteeMember;
use App\Models\Procurement\TenderSupplier;
use App\Models\Procurement\BidSubmission;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderSection;
use App\Models\Procurement\TenderCommittee;
use App\Enums\Core\PermissionEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EvaluatorDashboardController extends Controller
{
    /**
     * Show evaluator dashboard with responsive bids ready for evaluation
     */
     public function index()
    {
        $this->authorize(PermissionEnum::BidSubmissionRead);

        // Get current user ID for committee membership check (committees store User ID, not Employee ID)
        $currentUserId = Auth::id();

    // Determine if global setup is missing: committees or criteria
    $needsCommitteeSetup = !TenderCommittee::where('IsActive', 1)->exists();
    $needsCriteriaSetup = !TenderSection::where('IsActive', 1)->exists();

    // Get tenders where user is a committee member and has accepted (support dual mapping)
        $tenderIds = TenderCommitteeMember::where(function ($q) use ($currentUserId) {
                $q->where('UserID', $currentUserId)
                  ->orWhereHas('userByEmployee', function ($uq) use ($currentUserId) {
                      $uq->where('Id', $currentUserId);
                  });
            })
            ->where('IsActive', 1)
            ->where('Response', 1)
            ->pluck('TenderID');

        if ($tenderIds->isEmpty()) {
            return view('procurement.tendering.bidopeningandevaluation.evaluationdashboard.index', [
                'evaluationData' => collect(),
                'userRole' => 'No committee assignments',
                'message' => 'You are not assigned to any evaluation committees or have not accepted any appointments.',
                'needsCommitteeSetup' => $needsCommitteeSetup,
                'needsCriteriaSetup' => $needsCriteriaSetup
            ]);
        }

        // Get tenders with responsive bids ready for evaluation
        $tenders = Tender::with(['submissions', 'tenderSections.sections.criteria'])
            ->whereIn('Id', $tenderIds)
            ->whereHas('submissions', function ($query) {
                $query->where('BidStatus', 'responsive')
                      ->where('IsResponsive', true);
            })
            ->get();

        $evaluationData = collect();

        foreach ($tenders as $tender) {
            // Check if tender has evaluation sections configured
            $sectionsConfigured = $tender->tenderSections->isNotEmpty();
            $weightValidation = $sectionsConfigured ? TenderSection::validateWeightsForTender($tender->Id) : null;

            // Get user's role in this tender committee
            $userRole = TenderCommitteeMember::where('TenderID', $tender->Id)
                ->where('UserID', $currentUserId)
                ->value('Role') ?? 'Member';

            // Get responsive bids for this tender
            $responsiveBids = $tender->submissions()
                ->where('BidStatus', 'responsive')
                ->where('IsResponsive', true)
                ->with('supplier.thirdParty')
                ->get();

            foreach ($responsiveBids as $bid) {
                $evaluationStatus = $bid->getEvaluationStatus();

                $evaluationData->push([
                    'tender_id' => $tender->Id,
                    'tender_no' => $tender->TenderNo,
                    'tender_title' => $tender->Title,
                    'tender_status' => $tender->Status,
                    'sections_configured' => $sectionsConfigured,
                    'sections_count' => $tender->tenderSections->count(),
                    'weight_valid' => $weightValidation ? $weightValidation['is_valid'] : false,
                    'total_weight' => $weightValidation ? $weightValidation['total_weight'] : 0,
                    'user_role' => $userRole,
                    'has_evaluated' => TenderCommitteeMember::where('TenderID', $tender->Id)
                        ->where('UserID', $currentUserId)
                        ->value('HasEvaluated') ?? false,
                    'bid_id' => $bid->Id,
                    'supplier_name' => $bid->SupplierName,
                    'supplier_id' => $bid->SupplierId,
                    'bid_amount' => $bid->BidAmount,
                    'currency' => $bid->Currency,
                    'technical_score' => $bid->TechnicalScore,
                    'financial_score' => $bid->FinancialScore,
                    'total_score' => $bid->TotalScore,
                    'evaluation_status' => $evaluationStatus,
                    'evaluation_notes' => $bid->EvaluationNotes,
                    'can_evaluate' => $sectionsConfigured &&
                                    ($weightValidation ? $weightValidation['is_valid'] : false) &&
                                    $evaluationStatus['status'] !== 'non-responsive'
                ]);
            }
        }

        return view('procurement.tendering.bidopeningandevaluation.evaluationdashboard.index', [
            'evaluationData' => $evaluationData,
            'userRole' => 'Committee Member',
            'tenderCount' => $tenders->count(),
            'bidsCount' => $evaluationData->count(),
            'needsCommitteeSetup' => $needsCommitteeSetup,
            'needsCriteriaSetup' => $needsCriteriaSetup
        ]);
    }

    /**
     * Show detailed evaluation interface for a specific tender
     */
    public function showTenderEvaluation($tenderId)
    {
        $this->authorize(PermissionEnum::BidSubmissionRead);

        // Verify user is accepted & active committee member for this tender (supports direct TenderID or Committee ReferenceId)
        $currentUserId = Auth::id();
        $membership = DB::table('t_TenderCommitteeMembers as m')
            ->leftJoin('t_TenderCommittee as c', 'c.Id', '=', 'm.CommitteeID')
            ->join('t_Users as u', function($join){
                $join->on('u.Id', '=', 'm.UserID')
                     ->orOn('u.EmployeeId', '=', 'm.UserID');
            })
            ->where('u.Id', $currentUserId)
            ->where(function($q) use ($tenderId){
                $q->where('m.TenderID', $tenderId)
                  ->orWhere('c.ReferenceId', $tenderId);
            })
            ->where('m.IsActive', 1)
            ->where('m.Response', 1)
            ->whereNull('m.DeletedOn')
            ->select('m.Id')
            ->first();

        if (!$membership) {
            return redirect()->route('evaluationdashboard.index')
                ->with('error', 'You are not authorized to evaluate this tender.');
        }

        // Load the full committee member model for use in the view
        $committeeMember = TenderCommitteeMember::find($membership->Id);

        $tender = Tender::with(['tenderSections.sections.criteria', 'submissions'])
            ->findOrFail($tenderId);

        // Get evaluation readiness
        $readiness = $tender->getEvaluationReadiness();
        if (!$readiness['ready']) {
            return redirect()->route('evaluationdashboard.index')
                ->with('error', $readiness['message']);
        }

        // Get responsive bids
        $responsiveBids = $tender->submissions()
            ->where('BidStatus', 'responsive')
            ->where('IsResponsive', true)
            ->with('supplier.thirdParty')
            ->get();

        return view('procurement.tendering.bidopeningandevaluation.evaluationdashboard.tender-evaluation', [
            'tender' => $tender,
            'sections' => $tender->tenderSections, // TenderSection pivot models with weights
            'responsiveBids' => $responsiveBids,
            'committeeMember' => $committeeMember,
            'userRole' => $committeeMember->Role ?? 'Member'
        ]);
    }

    public function create(){
        return view('procurement.tendering.bidopeningandevaluation.evaluationdashboard.create');
    }
}
