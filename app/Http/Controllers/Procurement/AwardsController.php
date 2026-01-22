<?php

namespace App\Http\Controllers\Procurement;


use App\Http\Controllers\Controller;
use App\Models\Procurement\Tender;
use App\Enums\TenderStatusEnum;
use App\Models\Procurement\TenderAward;
use App\Models\Procurement\RFQAward;
use App\Models\Procurement\RFQEvaluation;
use App\Models\Procurement\RFQResponse;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\TenderSupplier;
use App\Models\Procurement\TenderCommitteeEvaluation;
use App\Models\Procurement\BidResponsiveness;
use App\Services\Workflow\ApprovalWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Procurement\TenderScoringService;

class AwardsController extends Controller
{

    protected ApprovalWorkflow $workflow;

    public function __construct()
    {
        // Initialize workflow with tender_award CodeID and AwardStatus column
        $this->workflow = new ApprovalWorkflow('TenderAwardStatus', 'AwardStatus');
    }
    /**
     * Display a listing of awards
     */
  public function index(Request $request)
    {
        $statusFilter = $request->get('status_filter');
        $search = $request->get('search');

        // Build Tender entries with workflow status
        $tenderAwards = TenderAward::with(['tender', 'winningSupplier.supplierMaster.party'])
            ->get()
            ->map(function ($award) {
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
                    'ref_no' => trim((($award->tender->TenderNo ?? '') . ' - ' . ($award->tender->Title ?? ''))) ?: 'N/A',
                    'title' => $award->tender->Title ?? 'N/A',
                    'status' => $status,
                    'status_class' => $statusClass,
                    'winning_bidder' => $award->winningSupplier->supplierMaster->party->TradingName ?? '--',
                    'award_date' => optional($award->AwardDate)->format('Y-m-d') ?? ($award->CreatedOn?->format('Y-m-d') ?? '--'),
                    'tender_id' => $award->tender->Id ?? $award->TenderID,
                    'award_id' => $award->Id,
                ];
            })
            ->values()
            ->toBase();

        // Tenders with evaluations but no award yet => Pending
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
        $rfqAwards = RFQAward::with(['rfq', 'supplier.supplierMaster.party'])
            ->get()
            ->map(function ($award) {
                return [
                    'type' => 'rfq',
                    'ref_no' => $award->rfq->RFQNumber ?? 'N/A',
                    'title' => $award->rfq->Subject ?? ($award->rfq->Comments ?? 'N/A'),
                    't_RFQ' => [
                        'RefNo' => $award->rfq->RFQNumber ?? '',
                        'Comments' => $award->rfq->Comments ?? ($award->rfq->Subject ?? ''),
                    ],
                    'status' => 'Awarded',
                    'status_class' => 'bg-success',
                    'winning_bidder' => $award->supplier->supplierMaster->party->TradingName ?? '--',
                    'award_date' => ($award->CreatedOn?->format('Y-m-d')) ?? '--',
                    'rfq_id' => $award->RFQId ?? ($award->rfq->Id ?? null),
                    'award_id' => $award->Id,
                ];
            })
            ->values()
            ->toBase();

        // RFQs with evaluations but no award => Pending
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

        // Apply status filter
        if ($statusFilter === 'Pending') {
            $items = $items->where('status', 'Pending')->values();
        } elseif ($statusFilter === 'Awarded') {
            $items = $items->where('status', 'Approved')->values();
        }

        // Sort by award_date desc
        $items = $items->sortByDesc(function($row){
            return $row['award_date'] === '--' ? '' : $row['award_date'];
        })->values();

        // Add permission checks for tender awards
        $user = Auth::user();
        
        // If user is not authenticated, redirect to login
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }
        
        $items = $items->map(function ($row) use ($user) {
            if ($row['type'] === 'tender' && isset($row['award_id'])) {
                $award = TenderAward::find($row['award_id']);
                if ($award) {
                    // Check if user can approve this award at current workflow level
                    $row['can_approve'] = $this->workflow->canApproveModel($award, $user);
                } else {
                    $row['can_approve'] = false;
                }
            } else {
                $row['can_approve'] = false;
            }
            return $row;
        });

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
        $tender = null;
        $existingAward = null;

        if ($type === 'rfq') {
            $rfq = RFQ::findOrFail($id);
            // Map RFQ to Tender-like structure for the view
            $rfq->TenderNo = $rfq->RFQNumber;
            $rfq->Title = $rfq->Subject ?? ($rfq->Comments ?? 'RFQ Award');
            $tender = $rfq;

            $existingAward = RFQAward::where('RFQId', $id)
                ->with(['supplier.supplierMaster.party'])
                ->orderByDesc('Id')
                ->first();
        } else {
            $tender = Tender::findOrFail($id);
            
            // Get most recent award
            $existingAward = TenderAward::where('TenderID', $id)
                ->with(['winningSupplier.supplierMaster.party'])
                ->orderByDesc('Id')
                ->first();
        }

        // Check if user can approve
        $canApprove = false;
        $isSubmitter = false;
        $showApprovalButtons = false;

        if ($existingAward) {
            $isSubmitter = $existingAward->CreatedBy == Auth::id();
            
            // Only check approval permissions if award is pending
            // Note: RFQAward doesn't have AwardStatus column by default in some versions, 
            // but if it does or if we treat existence as awarded, we need to be careful.
            // Assuming standard workflow for TenderAward. For RFQAward, it's usually created as approved/final in this system.
            
            $status = $existingAward->AwardStatus ?? 'Approved'; // Default to Approved for RFQ if column missing

            if ($status === 'Pending') {
                $currentUser = Auth::user();
                if ($currentUser) {
                    $canApprove = $this->workflow->canApproveModel($existingAward, $currentUser);
                    
                    // User who submitted cannot approve their own award
                    if ($isSubmitter) {
                        $canApprove = false;
                    }
                }
                
                $showApprovalButtons = $canApprove && !$isSubmitter;
            }
        }

        // Determine type if not specified (fallback for Tenders)
        if (!$type && $tender instanceof Tender) {
            $type = $this->determineTenderType($tender);
        }

        // Get evaluation data
        $evaluationData = [];
        if ($type === 'rfq') {
            $evaluationData = $this->getResponsiveRFQSuppliers($id);
        } else {
            $evaluationData = $this->getConsolidatedScores($id);
        }

        // Get workflow history if award exists
        $workflowHistory = [];
        if ($existingAward) {
            try {
                $workflowHistory = $this->workflow->historyForModel($existingAward);
            } catch (\Exception $e) {
                Log::warning("Failed to load workflow history: " . $e->getMessage());
            }
        }

        $availableItems = $this->getAvailableItemsForAward();

        return view('procurement.awards.unified_award', compact(
            'tender',
            'existingAward',
            'evaluationData',
            'type',
            'availableItems',
            'canApprove',
            'showApprovalButtons',
            'isSubmitter',
            'workflowHistory'
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
            // Check if active award exists
            $hasActiveAward = TenderAward::where('TenderID', $request->tender_id)
                ->whereIn('AwardStatus', ['Pending', 'Approved'])
                ->exists();
                
            if ($hasActiveAward) {
                return redirect()->back()->with('error', 'An active award already exists for this tender.');
            }

            // Create award in Pending status
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
                'AwardStatus' => 'Pending', // Start as pending (Draft not allowed by CHECK constraint)
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

            return redirect()->route('awards.tender', $request->tender_id)
                ->with('success', 'Tender award created successfully. Please review and submit for approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Award creation error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to create award: ' . $e->getMessage())
                ->withInput();
        }
    }

     public function submitForApproval($id)
    {
        try {
            DB::beginTransaction();
            
            $award = TenderAward::findOrFail($id);
            $user = Auth::user();

            // Validation: Must be in Draft or Pending status
            if (!in_array($award->AwardStatus, [TenderAward::STATUS_DRAFT, TenderAward::STATUS_PENDING])) {
                return redirect()->back()->with('error', 'Only draft or pending awards can be submitted for approval.');
            }

            // Submit to workflow
            $submitted = $this->workflow->submit(
                $award, 
                $user, 
                \App\Enums\TenderAwardStatusEnum::SUBMITTED,  // Use enum instead of string
                'Award submitted for approval'
            );

            if (!$submitted) {
                throw new \Exception('Failed to submit award to workflow');
            }

            // Manually update award status (workflow service doesn't auto-update the model)
            $award->update([
                'AwardStatus' => TenderAward::STATUS_SUBMITTED,
                'ModifiedBy' => $user->Id,
                'ModifiedOn' => now(),
            ]);

            DB::commit();

            activity()
                ->performedOn($award)
                ->causedBy($user)
                ->withProperties(['action' => 'submit_for_approval'])
                ->log('Submitted award for approval: Award ID ' . $award->Id);

            return redirect()->route('procawards.index')
                ->with('success', 'Award submitted for approval successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Award Submit Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to submit award: ' . $e->getMessage());
        }
    }


    /**
     * Approve award
     */
public function approve(Request $request)
    {
        $request->validate([
            'award_id' => 'required|exists:t_TenderAwards,Id',
            'remarks' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $award = TenderAward::findOrFail($request->award_id);
            $user = Auth::user();

            Log::info("Starting award approval", [
                'award_id' => $award->Id,
                'user_id' => $user->Id,
                'current_status' => $award->AwardStatus,
            ]);

            // Check if user can approve
            if (!$this->workflow->canApproveModel($award, $user)) {
                Log::warning("User not authorized to approve award", [
                    'award_id' => $award->Id,
                    'user_id' => $user->Id,
                ]);
                return redirect()->back()->with('error', 'You are not authorized to approve this award at this stage.');
            }

            // Validate award is in submitted/under review status
            if (!in_array($award->AwardStatus, [TenderAward::STATUS_SUBMITTED, TenderAward::STATUS_UNDER_REVIEW])) {
                return redirect()->back()->with('error', 'Only submitted awards can be approved.');
            }

            // Use workflow to approve
            $approved = $this->workflow->approve(
                $award,
                $user,
                \App\Enums\TenderAwardStatusEnum::APPROVED,
                $request->remarks,
                'AwardStatus' // Specify the status column
            );
            
            if (!$approved) {
                throw new \Exception('Workflow approval failed');
            }


            Log::info("Workflow approval successful", [
                'award_id' => $award->Id,
            ]);

            // Update award status
            $award->refresh();
            $award->update([
                'AwardStatus' => 'Approved',
                'ApprovedBy' => $user->Id,
                'ApprovedOn' => now(),
                'ModifiedBy' => $user->Id,
                'ModifiedOn' => now(),
            ]);

            // Update Tender Status to Awarded
            if ($award->tender) {
                $award->tender->update([
                    'Status' => \App\Enums\TenderStatusEnum::Awarded->value
                ]);
            }

            // Notify award creator
            $creator = \App\Models\Auth\User::find($award->CreatedBy);
            if ($creator) {
                $creator->notify(new \App\Notifications\Procurement\AwardApprovedNotification($award));
            }

            // Send notifications to unsuccessful bidders if requested
            if ($award->NotifyUnsuccessfulBidders) {
                $this->notifyUnsuccessfulBidders($award);
            }

            activity()
                ->performedOn($award)
                ->causedBy($user)
                ->withProperties([
                    'action' => 'approve',
                    'approval_remarks' => $request->remarks,
                ])
                ->log('Award approved: Award ID ' . $award->Id);

            DB::commit();

            return redirect()->route('procawards.index')
                ->with('success', 'Award approved successfully.');

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("--- APPROVE AWARD ERROR --- " . $th->getMessage());
            Log::error($th->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to approve award: ' . $th->getMessage());
        }
    }

    /**
     * Approve an RFQ by creating an RFQ award for the top ranked supplier.
     * Uses the same weighting logic applied in RFQEvaluationController for ranking.
     */
    public function approveRfq(Request $request, RFQ $rfq)
    {
        $request->validate([
            'approval_remarks' => 'nullable|string|max:500',
        ]);

        // If already awarded, just redirect (idempotent behaviour)
        $existing = RFQAward::where('RFQId', $rfq->Id)->first();
        if ($existing) {
            return redirect()->route('procawards.index')->with('info', 'RFQ already awarded.');
        }

        // Load evaluations + criteria weights
        $evaluations = RFQEvaluation::with([
            'evaluations.rfqCriteriaUnscoped.criteria',
            'evaluations.rfqCriteriaUnscoped.section',
            'evaluations.supplier.thirdParty',
        ])->where('RFQId', $rfq->Id)->get();

        if ($evaluations->isEmpty()) {
            return redirect()->route('procawards.index')->with('error', 'No evaluations found for this RFQ.');
        }

        // Section weights
        $sectionWeights = \DB::table('t_RFQSection')
            ->where('RFQID', $rfq->Id)
            ->pluck('Weight', 'SectionID')
            ->map(fn($w) => (float)$w)
            ->toArray();

        // Aggregate per supplier (similar to RFQEvaluationController@index logic)
        $supplierTotals = []; // [supplierId] => [sumWeightedAcrossEvaluators, evaluatorCount]
        foreach ($evaluations as $evaluation) {
            $bySupplier = $evaluation->evaluations->groupBy('SupplierId');
            foreach ($bySupplier as $supplierId => $entries) {
                $sectionGroups = $entries->groupBy(fn($e) => $e->rfqCriteriaUnscoped?->SectionID);
                $evaluatorWeightedTotal = 0.0;
                foreach ($sectionGroups as $sectionId => $criteriaList) {
                    $weight = (float)($sectionWeights[$sectionId] ?? 0);
                    $maxTotal = $criteriaList->count() * 10; // each criteria out of 10
                    $actualTotal = $criteriaList->sum('Score');
                    $sectionWeighted = $maxTotal > 0 ? (($actualTotal / $maxTotal) * $weight) : 0.0;
                    $evaluatorWeightedTotal += $sectionWeighted;
                }
                $supplierTotals[$supplierId]['sum'] = ($supplierTotals[$supplierId]['sum'] ?? 0) + $evaluatorWeightedTotal;
                $supplierTotals[$supplierId]['count'] = ($supplierTotals[$supplierId]['count'] ?? 0) + 1;
            }
        }

        // Determine top supplier (highest average weighted total)
        $topSupplierId = null;
        $topScore = -1;
        foreach ($supplierTotals as $sid => $agg) {
            $avg = $agg['count'] > 0 ? $agg['sum'] / $agg['count'] : 0.0;
            if ($avg > $topScore) { $topScore = $avg; $topSupplierId = (int)$sid; }
        }

        if (!$topSupplierId) {
            return redirect()->route('procawards.index')->with('error', 'Failed to determine top supplier for RFQ.');
        }

        // Persist award
        $award = RFQAward::create([
            'RFQId' => $rfq->Id,
            'SupplierId' => $topSupplierId,
            'Comments' => $request->input('approval_remarks'),
            'CreatedBy' => Auth::id(),
            'ModifiedBy' => Auth::id(),
        ]);

        activity()
            ->performedOn($award)
            ->causedBy(Auth::user())
            ->withProperties([
                'action' => 'approve-rfq',
                'rfq_id' => $rfq->Id,
                'supplier_id' => $topSupplierId,
                'score' => round($topScore,2),
            ])
            ->log('RFQ awarded to top ranked supplier.');

        return redirect()->route('procawards.index')->with('success', 'RFQ awarded successfully.');
    }

    /**
     * Reject award
     */
    public function reject(Request $request)
    {
        $request->validate([
            'award_id' => 'required|exists:t_TenderAward,Id',
            'reason' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $award = TenderAward::findOrFail($request->award_id);
            $user = Auth::user();

            Log::info("Starting award rejection", [
                'award_id' => $award->Id,
                'user_id' => $user->Id,
            ]);

            // Check if user can approve (same permission for reject)
            if (!$this->workflow->canApproveModel($award, $user)) {
                return redirect()->back()->with('error', 'You are not authorized to reject this award.');
            }

            // Validate award is in submitted/under review status
            if (!in_array($award->AwardStatus, [TenderAward::STATUS_SUBMITTED, TenderAward::STATUS_UNDER_REVIEW])) {
                return redirect()->back()->with('error', 'Only submitted awards can be rejected.');
            }

            // Use workflow to reject
            $rejected = $this->workflow->reject(
                $award,
                $user,
                \App\Enums\TenderAwardStatusEnum::REJECTED,
                $request->reason,
                'AwardStatus'
            );

            if (!$rejected) {
                throw new \Exception('Workflow rejection failed');
            }

            // Update award status
            $award->refresh();
            $award->update([
                'AwardStatus' => 'Rejected',
                'RejectionReason' => $request->reason,
                'ModifiedBy' => $user->Id,
                'ModifiedOn' => now(),
            ]);

            activity()
                ->performedOn($award)
                ->causedBy($user)
                ->withProperties([
                    'action' => 'reject',
                    'rejection_reason' => $request->reason,
                ])
                ->log('Award rejected: Award ID ' . $award->Id);

            DB::commit();

            return redirect()->route('procawards.index')
                ->with('success', 'Award rejected successfully.');

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("--- REJECT AWARD ERROR --- " . $th->getMessage());
            return redirect()->back()->with('error', 'Failed to reject award: ' . $th->getMessage());
        }
    }


    /**
     * Cancel a pending award (re-open tender for re-award)
     */
   public function workflowHistory($id)
    {
        $award = TenderAward::findOrFail($id);
        
        try {
            $history = $this->workflow->historyForModel($award);
            
            return view('procurement.awards.workflow-history', compact(
                'award',
                'history'
            ));
        } catch (\Exception $e) {
            Log::error('Failed to fetch workflow history: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load workflow history.');
        }
    }

    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $award = TenderAward::findOrFail($id);

            // Only allow cancel for non-approved awards
            if ($award->AwardStatus === 'Approved') {
                return back()->with('error', 'Approved awards cannot be cancelled.');
            }

            $award->update([
                'AwardStatus' => 'Cancelled',
                'RejectionReason' => $request->cancel_reason,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            activity()
                ->performedOn($award)
                ->causedBy(Auth::user())
                ->withProperties(['cancel_reason' => $request->cancel_reason])
                ->log('Award cancelled: Award ID ' . $award->Id);

            DB::commit();

            return back()->with('success', 'Award cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Award cancel error: ' . $e->getMessage());
            return back()->with('error', 'Failed to cancel award: ' . $e->getMessage());
        }
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
     * Get responsive suppliers for RFQ
     */
    protected function getResponsiveRFQSuppliers($rfqId)
    {
        return RFQResponse::where('RFQId', $rfqId)
            ->get()
            ->map(function ($response) {
                return [
                    'id' => $response->SupplierId,
                    'name' => $response->SupplierName ?? 'Unknown Supplier',
                    'quoted_amount' => $response->TotalPayable ?? 0,
                    'delivery_time' => $response->DurationDays . ' Days',
                    'payment_terms' => 'N/A', // Not in RFQResponse directly
                    'is_responsive' => true, // Assuming responsive if response exists for now
                ];
            })
            ->unique('id')
            ->values();
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

    /**
     * Send notifications to unsuccessful bidders
     */
    protected function notifyUnsuccessfulBidders($award)
    {
        try {
            // Get all bidders for this tender EXCEPT the winner
            $unsuccessfulBidders = \App\Models\Procurement\BidSubmission::where('TenderRef', $award->tender->TenderNo)
                ->where('SupplierId', '!=', $award->WinningSupplierID)
                ->where('IsResponsive', true) // Only notify responsive bidders who lost
                ->with('supplier.supplierMaster.thirdParty.users')
                ->get();
            
            $tenderTitle = $award->tender->Title ?? 'Tender';
            $tenderNo = $award->tender->TenderNo;
            
            foreach ($unsuccessfulBidders as $bidder) {
                // Get supplier primary contact
                $supplier = $bidder->supplier; // t_Suppliers
                $thirdParty = $supplier->supplierMaster->thirdParty; // SupplierMaster -> ThirdParty
                
                // Try to find a user/contact to email
                $contactUser = $thirdParty->users ? $thirdParty->users->first() : null; // Get first user as primary contact
                $email = $contactUser ? $contactUser->Email : ($thirdParty->Email ?? null);
                $name = $contactUser ? $contactUser->Name : ($thirdParty->ThirdPartyName ?? 'Supplier');
                
                if ($email) {
                    // Send Regret Letter via generic Mail
                    $subject = "Regret Letter - {$tenderTitle} ({$tenderNo})";
                    $body = "Dear {$name},<br><br>" .
                            "Thank you for participating in the tender <strong>{$tenderTitle} ({$tenderNo})</strong>.<br><br>" .
                            "We regret to inform you that your bid was not successful on this occasion. The contract has been awarded to another bidder.<br><br>" .
                            "We appreciate the time and effort you put into your submission and encourage you to participate in our future tenders.<br><br>" .
                            "Sincerely,<br>" .
                            "Procurement Department<br>" .
                            config('app.name');

                    try {
                        \Illuminate\Support\Facades\Mail::html($body, function($message) use ($email, $subject) {
                            $message->to($email)
                                    ->subject($subject);
                        });
                        Log::info("Regret email sent to {$email} for tender {$tenderNo}");
                    } catch (\Exception $e) {
                         Log::error("Failed to send email to {$email}: " . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to notify unsuccessful bidders: " . $e->getMessage());
        }
    }

}
