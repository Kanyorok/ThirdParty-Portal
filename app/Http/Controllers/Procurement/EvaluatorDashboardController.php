<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\TenderCommitteeMember;
use App\Models\Procurement\TenderSupplier;
use App\Models\Procurement\BidSubmission;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderSection;
use App\Enums\Core\PermissionEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Get tenders where user is a committee member and has accepted
        $tenderIds = TenderCommitteeMember::where('UserID', $currentUserId)
            ->where('Response', 1) // Accepted appointment
            ->pluck('TenderID');

        if ($tenderIds->isEmpty()) {
            return view('procurement.tendering.bidopeningandevaluation.evaluationdashboard.index', [
                'evaluationData' => collect(),
                'userRole' => 'No committee assignments',
                'message' => 'You are not assigned to any evaluation committees or have not accepted any appointments.'
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
            'bidsCount' => $evaluationData->count()
        ]);
    }

    /**
     * Show detailed evaluation interface for a specific tender
     */
    public function showTenderEvaluation($tenderId)
    {
        $this->authorize(PermissionEnum::BidSubmissionRead);

        // Verify user is committee member for this tender
        $currentUserId = Auth::id();
        $committeeMember = TenderCommitteeMember::where('TenderID', $tenderId)
            ->where('UserID', $currentUserId)
            ->where('Response', 1)
            ->first();

        if (!$committeeMember) {
            return redirect()->route('evaluationdashboard.index')
                ->with('error', 'You are not authorized to evaluate this tender.');
        }

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
            'userRole' => $committeeMember->Role
        ]);
    }

    public function create(){
        return view('procurement.tendering.bidopeningandevaluation.evaluationdashboard.create');
    }
}
