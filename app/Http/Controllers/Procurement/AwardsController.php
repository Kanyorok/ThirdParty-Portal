<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderAward;
use App\Models\Procurement\RFQAward;
use App\Models\Procurement\RFQEvaluation;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\TenderSupplier;
use App\Models\Procurement\TenderCommitteeEvaluation;
use App\Models\Procurement\BidResponsiveness;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Procurement\TenderScoringService;

class AwardsController extends Controller
{
    /**
     * Display a listing of awards
     */
    public function index(Request $request)
    {
        $statusFilter = $request->get('status_filter'); // Expected: 'Pending' | 'Awarded' | null
        $search = $request->get('search');

        // Build Tender entries
        $tenderAwards = TenderAward::with(['tender', 'winningSupplier.thirdParty'])
            ->get()
            ->map(function ($award) {
                // Use actual AwardStatus from database
                $status = $award->AwardStatus;
                $statusClass = match($status) {
                    'Pending' => 'bg-warning text-dark',
                    'Approved' => 'bg-success',
                    'Rejected' => 'bg-danger',
                    'Cancelled' => 'bg-secondary',
                    default => 'bg-light text-dark'
                };
                
                return [
                    'type' => 'tender',
                    // show TenderNo and Title together in the Ref column
                    'ref_no' => trim((($award->tender->TenderNo ?? '') . ' - ' . ($award->tender->Title ?? ''))) ?: 'N/A',
                    'title' => $award->tender->Title ?? 'N/A',
                    'status' => $status,
                    'status_class' => $statusClass,
                    'winning_bidder' => $award->winningSupplier->thirdParty->TradingName
                        ?? '--',
                    'award_date' => optional($award->AwardDate)->format('Y-m-d') ?? ($award->CreatedOn?->format('Y-m-d') ?? '--'),
                    // IDs
                    'tender_id' => $award->tender->Id ?? $award->TenderID,
                    'award_id' => $award->Id,
                ];
            })
            ->values()
            ->toBase();

        // Tenders with consolidated evaluations but no award yet => Pending
        $tendersWithEval = Tender::whereHas('submissions', function ($q) {
                $q->where('IsResponsive', true)->whereIn('BidStatus', ['responsive', 'evaluated']);
            })
            ->whereExists(function($q){
                $q->select(DB::raw(1))
                  ->from('t_TenderCommitteeEvaluations as e')
                  ->whereColumn('e.TenderID', 't_Tenders.Id');
            })
            ->with('award')
            ->get()
            ->filter(function ($t) { return !$t->award; })
            ->map(function ($tender) {
                return [
                    'type' => 'tender',
                    'ref_no' => trim((($tender->TenderNo ?? '') . ' - ' . ($tender->Title ?? ''))) ?: 'N/A',
                    'title' => $tender->Title,
                    'status' => 'Pending',
                    'status_class' => 'bg-warning text-dark',
                    'winning_bidder' => '--',
                    'award_date' => '--',
                    'id' => $tender->Id,
                ];
            })
            ->values()
            ->toBase();

        // RFQ awarded entries
        $rfqAwards = RFQAward::with(['rfq', 'supplier.thirdParty'])
            ->get()
            ->map(function ($award) {
                return [
                    'type' => 'rfq',
                    // keep top-level ref_no/title for backward compatibility
                    'ref_no' => $award->rfq->RFQNumber ?? 'N/A',
                    'title' => $award->rfq->Subject ?? ($award->rfq->Comments ?? 'N/A'),
                    // provide the raw t_RFQ shape expected by the blade
                    't_RFQ' => [
                        'RefNo' => $award->rfq->RFQNumber ?? '',
                        'Comments' => $award->rfq->Comments ?? ($award->rfq->Subject ?? ''),
                    ],
                    'status' => 'Awarded',
                    'status_class' => 'bg-success',
                    'winning_bidder' => $award->supplier->thirdParty->TradingName ?? '--',
                    'award_date' => ($award->CreatedOn?->format('Y-m-d')) ?? '--',
                    // IDs
                    'rfq_id' => $award->RFQId ?? ($award->rfq->Id ?? null),
                    'award_id' => $award->Id,
                ];
            })
            ->values()
            ->toBase();

        // RFQs with consolidated evaluations but no award => Pending
        $rfqsWithEval = RFQEvaluation::select('RFQId')
            ->distinct()
            ->get()
            ->pluck('RFQId');

        $rfqPending = RFQ::whereIn('Id', $rfqsWithEval)
            ->whereNotExists(function($q){
                $q->select(DB::raw(1))
                  ->from('t_RFQAward as a')
                  ->whereColumn('a.RFQId', 't_RFQ.Id');
            })
            ->get()
            ->map(function ($rfq) {
                return [
                    'type' => 'rfq',
                    'ref_no' => $rfq->RFQNumber ?? 'N/A',
                    'title' => $rfq->Subject ?? ($rfq->Comments ?? 'N/A'),
                    't_RFQ' => [
                        'RefNo' => $rfq->RFQNumber ?? '',
                        'Comments' => $rfq->Comments ?? ($rfq->Subject ?? ''),
                    ],
                    'status' => 'Pending',
                    'status_class' => 'bg-warning text-dark',
                    'winning_bidder' => '--',
                    'award_date' => '--',
                    'id' => $rfq->Id,
                ];
            })
            ->values()
            ->toBase();

        // Merge all
        $items = collect()
            ->merge($tenderAwards)
            ->merge($tendersWithEval)
            ->merge($rfqAwards)
            ->merge($rfqPending)
            ->values();

        // Apply search
        if ($search) {
            $needle = mb_strtolower($search);
            $items = $items->filter(function ($row) use ($needle) {
                return str_contains(mb_strtolower($row['ref_no']), $needle)
                    || str_contains(mb_strtolower($row['title']), $needle)
                    || str_contains(mb_strtolower($row['winning_bidder'] ?? ''), $needle);
            })->values();
        }

        // Apply simplified status filter
        if ($statusFilter === 'Pending') {
            $items = $items->where('status', 'Pending')->values();
        } elseif ($statusFilter === 'Awarded') {
            // Map "Awarded" filter to "Approved" status for compatibility
            $items = $items->where('status', 'Approved')->values();
        }

        // Sort by award_date desc, then ref_no
        $items = $items->sortByDesc(function($row){
            return $row['award_date'] === '--' ? '' : $row['award_date'];
        })->values();

        return view('procurement.awards.index', [
            'items' => $items,
            'filters' => $request->only(['status_filter', 'search'])
        ]);
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
        // Prefer the most recent award record for display (latest by Id)
        $existingAward = TenderAward::where('TenderID', $id)
            ->orderByDesc('Id')
            ->first();

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
        return Tender::whereHas('submissions', function ($query) {
            $query->where('IsResponsive', true)
                  ->whereIn('BidStatus', ['responsive', 'evaluated']);
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
            // Check if an active award exists (Pending/Approved)
            $hasActiveAward = TenderAward::where('TenderID', $request->tender_id)
                ->whereIn('AwardStatus', [TenderAward::STATUS_PENDING, TenderAward::STATUS_APPROVED])
                ->exists();
            if ($hasActiveAward) {
                return redirect()->back()->with('error', 'An active award already exists for this tender.');
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
     * Cancel a pending award (re-open tender for re-award)
     */
    public function cancel(Request $request, TenderAward $award)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        // Only allow cancel for non-approved awards
        if ($award->AwardStatus === TenderAward::STATUS_APPROVED) {
            return back()->with('error', 'Approved awards cannot be cancelled.');
        }

        $award->cancel(Auth::user(), $request->cancel_reason);

        return back()->with('success', 'Award cancelled. You can proceed to create a new award.');
    }

    /**
     * Get consolidated scores for tender award (using same logic as BidScoreConsolidationController)
     */
    protected function getConsolidatedScores($tenderId)
    {
        $tender = Tender::findOrFail($tenderId);

        // Get responsive bids from BidSubmissions table (including evaluated ones)
        $bidders = \App\Models\Procurement\BidSubmission::where('TenderRef', $tender->TenderNo)
            ->where('IsResponsive', true)
            ->whereIn('BidStatus', ['responsive', 'evaluated'])
            ->select('Id', 'SupplierId', 'SupplierName', 'BidAmount', 'Currency')
            ->get()
            ->map(function ($bid) {
                return [
                    'id' => $bid->SupplierId,
                    'bid_id' => $bid->Id,
                    'name' => $bid->SupplierName ?? 'Unknown Supplier',
                    'bid_amount' => $bid->BidAmount,
                    'currency' => $bid->Currency
                ];
            });

        // Shared computation to ensure parity with consolidation page
        $sections = $this->getTenderSections($tenderId);
        $computed = TenderScoringService::compute((int)$tenderId);

        $consolidatedScores = [];
        foreach ($bidders as $bidder) {
            $sid = (int)$bidder['id'];
            $entry = $computed[$sid] ?? null;
            if (!$entry) { continue; }

            $sectionScores = [];
            foreach ($sections as $sec) {
                $secId = (int)$sec['id'];
                $avg = (float)($entry['section_avgs'][$secId] ?? 0);
                $sectionScores[] = [
                    'section_id' => $secId,
                    'section_name' => $sec['name'],
                    'score' => $avg,
                    'weight' => (float)$sec['weight'],
                    'weighted_score' => ($avg * (float)$sec['weight']) / 100.0,
                ];
            }

            $consolidatedScores[] = [
                'bidder_id' => $bidder['id'],
                'bid_id' => $bidder['bid_id'],
                'bidder_name' => $bidder['name'],
                'bid_amount' => $bidder['bid_amount'],
                'currency' => $bidder['currency'],
                'section_scores' => $sectionScores,
                'total_weighted_score' => (float)$entry['final'],
                'technical_score' => $this->getTechnicalScore($sectionScores),
                'financial_score' => $this->getFinancialScore($sectionScores),
            ];
        }

        // Sort bidders by total score (highest first)
        usort($consolidatedScores, function ($a, $b) {
            return $b['total_weighted_score'] <=> $a['total_weighted_score'];
        });

        // Add rankings
        $rank = 1;
        foreach ($consolidatedScores as &$bidderScore) {
            $bidderScore['rank'] = $rank++;
            $bidderScore['recommendation'] = $this->getRecommendation($bidderScore['rank'], $bidderScore['total_weighted_score']);
        }

        return $consolidatedScores;
    }

    /**
     * Get tender sections with their criteria and weights (same as BidScoreConsolidationController)
     */
    protected function getTenderSections($tenderId)
    {
        // Pull real sections and tender-specific criteria with MaxScore
        $tenderSections = \App\Models\Procurement\TenderSection::where('TenderID', $tenderId)
            ->where('IsActive', true)
            ->whereNull('DeletedOn') // guard even though SoftDeletes should handle it
            ->with(['sections'])
            ->orderBy('Id', 'desc') // ensure latest mapping wins when deduping
            ->get()
            ->unique('SectionID') // one row per section
            ->sortByDesc('Weight')
            ->values();

        return $tenderSections
            ->filter(fn($ts) => $ts->sections)
            ->map(function ($ts) use ($tenderId) {
                $section = $ts->sections;

                // Retrieve tender-specific criteria rows to get MaxScore
                $tcRows = \App\Models\Procurement\TenderCriteria::where('TenderID', $tenderId)
                    ->where('SectionID', $section->Id)
                    ->where('IsActive', true)
                    ->whereNull('DeletedOn')
                    ->with('criteria')
                    ->get();

                $criteria = $tcRows->map(function($tc){
                    return [
                        'id' => $tc->CriteriaID,
                        'name' => $tc->criteria?->CriteriaName ?? 'Criteria',
                        'max_score' => (float) ($tc->MaxScore ?? 0),
                    ];
                });

                return [
                    'id' => $section->Id,
                    'name' => $section->SectionName,
                    'weight' => (float)($ts->Weight ?? 100),
                    'criteria' => $criteria,
                ];
            })
            ->values();
    }

    /**
     * Calculate bidder score for award (same logic as BidScoreConsolidationController)
     */
    protected function calculateBidderScoreForAward($bidder, $sections, $evaluations, $tenderId)
    {
        // Compute per-evaluator totals, then average them (requested math)
        $supplierGroup = $evaluations->get((int)$bidder['id']) ?? collect();

        $memberTotals = [];
        $sectionPercentSums = [];
        $memberCount = 0;

        foreach ($supplierGroup as $memberId => $memberData) {
            $memberCount++;
            $memberTotal = 0.0;
            foreach ($sections as $section) {
                $secPercent = $this->memberSectionPercent($memberData, $section);
                $sectionPercentSums[$section['id']] = ($sectionPercentSums[$section['id']] ?? 0.0) + $secPercent;
                $memberTotal += ($secPercent * $section['weight']) / 100.0;
            }
            $memberTotals[] = $memberTotal;
        }

        $avgTotal = count($memberTotals) > 0 ? array_sum($memberTotals) / count($memberTotals) : 0.0;

        // Section display rows: average section percent across evaluators
        $sectionScores = [];
        foreach ($sections as $section) {
            $avgSection = $memberCount > 0 ? (($sectionPercentSums[$section['id']] ?? 0.0) / $memberCount) : 0.0;
            $sectionScores[] = [
                'section_id' => $section['id'],
                'section_name' => $section['name'],
                'score' => $avgSection,
                'weight' => $section['weight'],
                'weighted_score' => ($avgSection * $section['weight']) / 100.0,
            ];
        }

        return [
            'bidder_id' => $bidder['id'],
            'bid_id' => $bidder['bid_id'],
            'bidder_name' => $bidder['name'],
            'bid_amount' => $bidder['bid_amount'],
            'currency' => $bidder['currency'],
            'section_scores' => $sectionScores,
            'total_weighted_score' => round($avgTotal, 2),
            'technical_score' => $this->getTechnicalScore($sectionScores),
            'financial_score' => $this->getFinancialScore($sectionScores)
        ];
    }

    /**
     * Calculate average score for a section across all evaluators (same as BidScoreConsolidationController)
     */
    protected function calculateSectionScoreForAward($section, $evaluations, $tenderId, int $supplierId)
    {
        // Keep for compatibility; delegate to member-based helper and average
        $supplierGroup = $evaluations->get($supplierId) ?? collect();
        if ($supplierGroup->isEmpty()) { return 0.0; }
        $sum = 0.0; $cnt = 0;
        foreach ($supplierGroup as $memberData) {
            $sum += $this->memberSectionPercent($memberData, $section);
            $cnt++;
        }
        return $cnt > 0 ? $sum / $cnt : 0.0;
    }

    private function memberSectionPercent($memberData, array $section): float
    {
        $sumScore = 0.0; $sumMax = 0.0;
        $sectionRows = optional($memberData)->get($section['id']) ?? collect();
        foreach ($section['criteria'] as $criteria) {
            $rows = optional($sectionRows)->get($criteria['id']) ?? collect();
            if ($rows->isEmpty()) { continue; }
            $avg = (float)$rows->avg('Score');
            $max = (float)($criteria['max_score'] ?? ($rows->avg('MaxScore') ?? 10));
            $sumScore += $avg;
            $sumMax += $max;
        }
        return $sumMax > 0 ? ($sumScore / $sumMax) * 100.0 : 0.0;
    }

    /**
     * Extract technical score from section scores
     */
    protected function getTechnicalScore($sectionScores)
    {
        $technicalSection = collect($sectionScores)->first(function($section) {
            return stripos($section['section_name'], 'technical') !== false;
        });

        return $technicalSection ? $technicalSection['score'] : null;
    }

    /**
     * Extract financial score from section scores
     */
    protected function getFinancialScore($sectionScores)
    {
        $financialSection = collect($sectionScores)->first(function($section) {
            return stripos($section['section_name'], 'financial') !== false ||
                   stripos($section['section_name'], 'finance') !== false;
        });

        return $financialSection ? $financialSection['score'] : null;
    }

    /**
     * Get recommendation based on rank and score
     */
    protected function getRecommendation($rank, $score)
    {
        if ($rank === 1 && $score >= 70) {
            return ['status' => 'Recommended', 'class' => 'success'];
        } elseif ($rank === 2 && $score >= 60) {
            return ['status' => 'Backup', 'class' => 'secondary'];
        } else {
            return ['status' => 'Not Recommended', 'class' => 'danger'];
        }
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
     * Direct redirect from consolidated scores to award page
     */
    public function createFromConsolidation($tenderId)
    {
        $tender = Tender::findOrFail($tenderId);

        // Verify tender is ready for award (has evaluations completed)
        $evaluationCount = TenderCommitteeEvaluation::where('TenderID', $tenderId)->count();
        if ($evaluationCount === 0) {
            return redirect()->back()->with('error', 'No evaluations found for this tender. Please complete evaluations first.');
        }

        // Block only if there is an active award (Pending/Approved)
        $hasActive = TenderAward::where('TenderID', $tenderId)
            ->whereIn('AwardStatus', [TenderAward::STATUS_PENDING, TenderAward::STATUS_APPROVED])
            ->exists();
        if ($hasActive) {
            return redirect()->route('awards.tender', $tenderId)
                ->with('info', 'This tender already has an active award.');
        }

        // Redirect to unified award interface
        return redirect()->route('awards.tender', $tenderId)
            ->with('success', 'Tender forwarded for award processing. Review the consolidated scores below and proceed with award creation.');
    }

    /**
     * Show create award form
     */
    public function create()
    {
        // Get tenders that are eligible for award (have completed evaluations)
        $tenders = Tender::whereHas('submissions', function ($query) {
            $query->where('IsResponsive', true)
                  ->whereIn('BidStatus', ['responsive', 'evaluated']);
        })->whereDoesntHave('awards')->get();

        return view('procurement.awards.create', compact('tenders'));
    }
}
