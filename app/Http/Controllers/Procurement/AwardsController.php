<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderAward;
use App\Models\Procurement\TenderSupplier;
use App\Models\Procurement\TenderCommitteeEvaluation;
use App\Models\Procurement\BidResponsiveness;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AwardsController extends Controller
{
    /**
     * Display a listing of awards
     */
    public function index(Request $request)
    {
        $query = TenderAward::with(['tender', 'winningSupplier', 'approvedBy']);

        // Apply filters
        if ($request->filled('status_filter')) {
            $query->where('AwardStatus', $request->status_filter);
        }

        if ($request->filled('search')) {
            $query->whereHas('tender', function ($q) use ($request) {
                $q->where('TenderNo', 'like', '%' . $request->search . '%')
                  ->orWhere('Title', 'like', '%' . $request->search . '%');
            });
        }

        $awards = $query->orderBy('CreatedOn', 'desc')->paginate(15);
        
        return view('procurement.awards.index', compact('awards'))
            ->with('filters', $request->only(['status_filter', 'search']));
    }

    /**
     * Show unified awards page for both Tenders and RFQs
     */
    public function view_tender($id)
    {
        return $this->showUnifiedAward($id, 'tender');
    }

    /**
     * Show RFQ award page (redirects to unified interface)
     */
    public function view_rfq($id)
    {
        return $this->showUnifiedAward($id, 'rfq');
    }

    /**
     * Unified award interface for both Tenders and RFQs
     */
    public function showUnifiedAward($id, $type = null)
    {
        $tender = Tender::findOrFail($id);
        $existingAward = TenderAward::where('TenderID', $id)->first();
        
        // Determine the type if not specified
        if (!$type) {
            $type = $this->determineTenderType($tender);
        }
        
        // Get appropriate data based on type
        $evaluationData = [];
        if ($type === 'rfq') {
            $evaluationData = $this->getResponsiveSuppliers($id);
        } else {
            $evaluationData = $this->getConsolidatedScores($id);
        }
        
        // Get list of similar tenders/RFQs for type switching
        $availableItems = $this->getAvailableItemsForAward();
        
        return view('procurement.awards.unified_award', compact(
            'tender',
            'existingAward',
            'evaluationData',
            'type',
            'availableItems'
        ));
    }

    /**
     * API endpoint to switch between tender types
     */
    public function switchType(Request $request)
    {
        $request->validate([
            'tender_id' => 'required|exists:t_Tenders,Id',
            'type' => 'required|in:tender,rfq'
        ]);

        return $this->showUnifiedAward($request->tender_id, $request->type);
    }

    /**
     * Get available items for award (both tenders and RFQs)
     */
    protected function getAvailableItemsForAward()
    {
        return Tender::whereHas('tenderSuppliers.bidResponsiveness', function ($query) {
            $query->where('IsResponsive', true);
        })
        ->with(['award'])
        ->get()
        ->map(function ($tender) {
            return [
                'id' => $tender->Id,
                'number' => $tender->TenderNo,
                'title' => $tender->Title,
                'type' => $this->determineTenderType($tender),
                'has_award' => $tender->award !== null,
                'award_status' => $tender->award ? $tender->award->AwardStatus : null,
            ];
        })
        ->groupBy('type');
    }

    /**
     * Determine if tender is RFQ or regular tender based on business logic
     */
    protected function determineTenderType($tender)
    {
        // You can customize this logic based on your business rules
        // For example, check TenderType, amount thresholds, or naming conventions
        
        if (str_contains(strtoupper($tender->TenderNo), 'RFQ')) {
            return 'rfq';
        }
        
        // Could also check estimated value, procurement mode, etc.
        return 'tender';
    }

    /**
     * Store award decision
     */
    public function store(Request $request)
    {
        $request->validate([
            'tender_id' => 'required|exists:t_Tenders,Id',
            'winning_supplier_id' => 'required|exists:t_Suppliers,Id',
            'award_justification' => 'required|string|max:1000',
            'awarded_amount' => 'nullable|numeric|min:0',
            'contract_start_date' => 'nullable|date|after_or_equal:today',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'notify_unsuccessful' => 'boolean',
            'technical_score' => 'nullable|numeric|min:0|max:100',
            'financial_score' => 'nullable|numeric|min:0|max:100',
            'total_score' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        
        try {
            // Check if award already exists
            $existingAward = TenderAward::where('TenderID', $request->tender_id)->first();
            if ($existingAward) {
                return redirect()->back()->with('error', 'Award already exists for this tender.');
            }

            $award = TenderAward::create([
                'TenderID' => $request->tender_id,
                'WinningSupplierID' => $request->winning_supplier_id,
                'AwardedAmount' => $request->awarded_amount,
                'AwardJustification' => $request->award_justification,
                'AwardDate' => now()->toDateString(),
                'ContractStartDate' => $request->contract_start_date,
                'ContractEndDate' => $request->contract_end_date,
                'TechnicalScore' => $request->technical_score,
                'FinancialScore' => $request->financial_score,
                'TotalScore' => $request->total_score,
                'NotifyUnsuccessfulBidders' => $request->boolean('notify_unsuccessful', true),
                'AwardStatus' => TenderAward::STATUS_PENDING,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
            ]);

            // Log activity
            activity()
                ->causedBy(Auth::id())
                ->performedOn($award)
                ->event('created')
                ->log('Created tender award for tender ID: ' . $request->tender_id);

            DB::commit();

            return redirect()->route('procawards.index')
                ->with('success', 'Tender award created successfully and is pending approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create award: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Approve award
     */
    public function approve(Request $request, TenderAward $award)
    {
        $request->validate([
            'approval_remarks' => 'nullable|string|max:500',
        ]);

        $award->approve(Auth::user(), $request->approval_remarks);

        // TODO: Send notifications to suppliers

        return redirect()->route('procawards.index')
            ->with('success', 'Award approved successfully.');
    }

    /**
     * Reject award
     */
    public function reject(Request $request, TenderAward $award)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $award->reject(Auth::user(), $request->rejection_reason);

        return redirect()->route('procawards.index')
            ->with('success', 'Award rejected successfully.');
    }

    /**
     * Get consolidated scores for tender award
     */
    protected function getConsolidatedScores($tenderId)
    {
        // Get only responsive bidders
        $bidders = TenderSupplier::where('TenderID', $tenderId)
            ->with(['supplier', 'bidResponsiveness'])
            ->whereHas('bidResponsiveness', function ($query) {
                $query->where('IsResponsive', true);
            })
            ->get();

        $scores = [];
        
        foreach ($bidders as $bidder) {
            // Calculate scores from evaluations
            $evaluations = TenderCommitteeEvaluation::where('TenderID', $tenderId)
                ->get()
                ->groupBy(['SectionID', 'CriteriaID']);
            
            // This is a simplified calculation - you might want to use the full logic from BidScoreConsolidationController
            $technicalScore = $this->calculateSectionScore($evaluations, 'technical');
            $financialScore = $this->calculateSectionScore($evaluations, 'financial');
            $totalScore = ($technicalScore + $financialScore) / 2;

            $scores[] = [
                'supplier' => $bidder->supplier,
                'technical_score' => round($technicalScore, 2),
                'financial_score' => round($financialScore, 2),
                'total_score' => round($totalScore, 2),
                'is_responsive' => $bidder->bidResponsiveness->IsResponsive ?? false,
            ];
        }

        // Sort by total score (highest first)
        usort($scores, function ($a, $b) {
            return $b['total_score'] <=> $a['total_score'];
        });

        return $scores;
    }

    /**
     * Get responsive suppliers for RFQ
     */
    protected function getResponsiveSuppliers($tenderId)
    {
        return TenderSupplier::where('TenderID', $tenderId)
            ->with(['supplier.thirdParty', 'bidResponsiveness'])
            ->whereHas('bidResponsiveness', function ($query) {
                $query->where('IsResponsive', true);
            })
            ->get()
            ->map(function ($tenderSupplier) {
                return [
                    'id' => $tenderSupplier->supplier->Id,
                    'name' => $tenderSupplier->supplier->thirdParty->ThirdPartyName ?? 'Unknown Supplier',
                    'quoted_amount' => 0, // TODO: Get from bid submissions
                    'delivery_time' => 'N/A', // TODO: Get from bid submissions  
                    'payment_terms' => 'N/A', // TODO: Get from bid submissions
                    'is_responsive' => $tenderSupplier->bidResponsiveness->IsResponsive ?? false,
                ];
            });
    }

    /**
     * Simple section score calculation
     */
    protected function calculateSectionScore($evaluations, $sectionType)
    {
        // This is a placeholder - implement proper section score calculation
        // based on your business logic
        return rand(60, 95); // Random score for demo purposes
    }

    /**
     * Show create award form
     */
    public function create()
    {
        // Get tenders that are eligible for award (have completed evaluations)
        $tenders = Tender::whereHas('tenderSuppliers.bidResponsiveness', function ($query) {
            $query->where('IsResponsive', true);
        })->whereDoesntHave('awards')->get();

        return view('procurement.awards.create', compact('tenders'));
    }
}
