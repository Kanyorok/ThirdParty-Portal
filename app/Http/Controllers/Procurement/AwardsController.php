<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\BidSubmission;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQAward;
use App\Models\Procurement\RFQCommittee;
use App\Models\Procurement\RFQCommitteeMember;
use App\Models\Procurement\RFQCriteria;
use App\Models\Procurement\RFQEvaluation;
use App\Models\Procurement\RFQResponse;
use App\Models\Procurement\RFQSupplierResponseEvaluation;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderAward;
use App\Models\Procurement\TenderCommittee;
use App\Models\Procurement\TenderCommitteeEvaluation;
use App\Models\Procurement\TenderCommitteeMember;
use App\Models\Procurement\TenderSupplier;
use App\Services\Procurement\TenderScoringService;
use App\Services\Workflow\ApprovalWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AwardsController extends Controller
{
    protected ApprovalWorkflow $workflow;
    protected ApprovalWorkflow $rfqWorkflow;

    public function __construct()
    {
        // Initialize workflow with tender_award CodeID and AwardStatus column
        $this->workflow = new ApprovalWorkflow('TenderAwardStatus', 'AwardStatus');
        $this->rfqWorkflow = new ApprovalWorkflow('rfq_award', 'AwardStatus');
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
                $statusClass = match ($status) {
                    'Pending' => 'bg-warning text-dark',
                    'Approved' => 'bg-success',
                    'Rejected' => 'bg-danger',
                    'Cancelled' => 'bg-secondary',
                    default => 'bg-light text-dark'
                };

                // Safely resolve winning bidder name
                $winningBidder = $award->winningSupplier?->supplierMaster?->party?->TradingName
                    ?? $award->winningSupplier?->supplierMaster?->party?->ThirdPartyName
                    ?? ($award->winningSupplier?->SupplierName ?? 'Supplier #'.$award->WinningSupplierID);

                // Safely resolve award date
                $awardDate = optional($award->AwardDate)->format('Y-m-d')
                    ?? ($award->CreatedOn?->format('Y-m-d') ?? '--');

                return [
                    'type' => 'tender',
                    'ref_no' => trim((($award->tender->TenderNo ?? '') . ' - ' . ($award->tender->Title ?? ''))) ?: 'N/A',
                    'title' => $award->tender->Title ?? 'N/A',
                    'status' => $status,
                    'status_class' => $statusClass,
                    'winning_bidder' => $winningBidder,
                    'award_date' => $awardDate,
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
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('t_TenderCommitteeEvaluations as e')
                    ->whereColumn('e.TenderID', 't_Tenders.Id');
            })
            ->with('award')
            ->get()
            ->filter(function ($t) {
                return ! $t->award;
            })
            ->map(function ($tender) {
                return [
                    'type' => 'tender',
                    'ref_no' => trim((($tender->TenderNo ?? '') . ' - ' . ($tender->Title ?? ''))) ?: 'N/A',
                    'title' => $tender->Title,
                    'status' => 'Pending',
                    'status_class' => 'bg-warning text-dark',
                    'winning_bidder' => '--',
                    'award_date' => '--',
                    'tender_id' => $tender->Id,
                ];
            })
            ->values()
            ->toBase();

        // RFQ awarded entries
        $rfqAwards = RFQAward::with(['rfq', 'supplier.supplierMaster.party'])
            ->get()
            ->map(function ($award) {
                $status = $award->AwardStatus ?: 'Pending';
                $statusClass = match ($status) {
                    'Pending' => 'bg-warning text-dark',
                    'Submitted for Approval' => 'bg-info text-dark',
                    'Under Review' => 'bg-primary',
                    'Approved' => 'bg-success',
                    'Rejected' => 'bg-danger',
                    'Cancelled' => 'bg-secondary',
                    default => 'bg-light text-dark'
                };

                $winningBidder = $award->supplier?->supplierMaster?->party?->TradingName
                    ?? $award->supplier?->supplierMaster?->party?->ThirdPartyName
                    ?? ($award->supplier?->SupplierName ?? 'Supplier #'.($award->SupplierId ?? ''));

                return [
                    'type' => 'rfq',
                    'ref_no' => $award->rfq->RFQNumber ?? 'N/A',
                    'title' => $award->rfq->Subject ?? ($award->rfq->Comments ?? 'N/A'),
                    't_RFQ' => [
                        'RefNo' => $award->rfq->RFQNumber ?? '',
                        'Comments' => $award->rfq->Comments ?? ($award->rfq->Subject ?? ''),
                    ],
                    'status' => $status,
                    'status_class' => $statusClass,
                    'winning_bidder' => $winningBidder,
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
            ->whereNotExists(function ($q) {
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
                    'rfq_id' => $rfq->Id,
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
        $items = $items->sortByDesc(function ($row) {
            return $row['award_date'] === '--' ? '' : $row['award_date'];
        })->values();

        // Add permission checks for tender awards
        $user = Auth::user();

        // If user is not authenticated, redirect to login
        if (! $user) {
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
            } elseif ($row['type'] === 'rfq' && isset($row['award_id'])) {
                $award = RFQAward::find($row['award_id']);
                if ($award) {
                    // Check if user can approve this award using RFQ workflow
                    $row['can_approve'] = $this->rfqWorkflow->canApproveModel($award, $user);
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
            'filters' => $request->only(['status_filter', 'search']),
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
                    if ($type === 'rfq') {
                        $canApprove = $this->rfqWorkflow->canApproveModel($existingAward, $currentUser);
                    } else {
                        $canApprove = $this->workflow->canApproveModel($existingAward, $currentUser);
                    }

                    // User who submitted cannot approve their own award
                    if ($isSubmitter) {
                        $canApprove = false;
                    }
                }

                $showApprovalButtons = $canApprove && ! $isSubmitter;
            }
        }

        // Determine type if not specified (fallback for Tenders)
        if (! $type && $tender instanceof Tender) {
            $type = $this->determineTenderType($tender);
        }

        // Get evaluation data
        $evaluationData = [];
        if ($type === 'rfq') {
            $evaluationData = $this->getResponsiveRFQSuppliers($id);
        } else {
            $evaluationData = $this->getConsolidatedScores($id);
        }

        // Check evaluation completeness (for both tenders and RFQs)
        $evaluationCompleteness = ['is_complete' => true];
        if ($type === 'tender') {
            $evaluationCompleteness = $this->checkEvaluationCompleteness($id);
        } elseif ($type === 'rfq') {
            $evaluationCompleteness = $this->checkRFQEvaluationCompleteness($id);
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
            'evaluationCompleteness',
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
            'type' => 'required|in:tender,rfq',
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
     * Store award decision (Unified for Tender and RFQ)
     */
    public function store(Request $request)
    {
        // Consolidation can submit SupplierMaster/ThirdParty keys in some legacy records.

        $type = $request->input('award_type', 'tender');
        // Normalize to t_Suppliers.Id before validation.
        $normalizedSupplierId = $this->resolveWinningSupplierId(
            $request->input('winning_supplier_id'),
            $request->input('tender_id')
        );
        if ($normalizedSupplierId !== null) {
            $request->merge(['winning_supplier_id' => $normalizedSupplierId]);
        }

        $request->validate([
            'tender_id' => 'required|exists:t_Tenders,Id',
            'winning_supplier_id' => 'required|exists:t_Suppliers,Id',
            'award_justification' => 'required|string|max:1000',
            'awarded_amount' => 'nullable|numeric|min:0',
            'contract_start_date' => 'nullable|date|after_or_equal:today',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'notify_unsuccessful' => 'boolean',
        ]);

        Log::info('Request data:', $request->all());

        $type = $request->input('award_type', 'tender');
        Log::info('Award type:', ['type' => $type]);

        $rules = [
            'award_type' => 'required|in:tender,rfq',
            'winning_supplier_id' => 'required|exists:t_Suppliers,Id',
            'award_justification' => 'required|string|max:1000',
            'awarded_amount' => 'nullable|numeric|min:0',
            'contract_start_date' => 'nullable|date|after_or_equal:today',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'notify_unsuccessful' => 'boolean',
        ];

        // Specific validation based on type
        if ($type === 'rfq') {
            $rules['tender_id'] = 'required|exists:t_RFQ,Id';
        } else {
            $rules['tender_id'] = 'required|exists:t_Tenders,Id';
        }

        Log::info('Validation rules:', $rules);

        try {
            $request->validate($rules);
            Log::info('Validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', $e->errors());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        }

        DB::beginTransaction();
        Log::info('Transaction started');

        try {
            $user = Auth::user();
            Log::info('User:', ['user_id' => $user->Id]);

            $id = $request->tender_id;
            Log::info('Tender/RFQ ID:', ['id' => $id]);

            if ($type === 'rfq') {
                Log::info('Processing RFQ award...');
                // RFQ logic...
            } else {
                Log::info('Processing Tender award...');

                // Check if active award exists
                $hasActiveAward = TenderAward::where('TenderID', $id)
                    ->whereIn('AwardStatus', ['Pending', 'Approved', 'Submitted for Approval', 'Under Review'])
                    ->exists();

                Log::info('Active award check:', ['has_active' => $hasActiveAward]);

                if ($hasActiveAward) {
                    DB::rollBack();
                    Log::warning('Active award already exists, rolling back');

                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'An active award already exists for this tender.',
                        ], 400);
                    }

                    return redirect()->back()->with('error', 'An active award already exists for this tender.');
                }

                // Gate: All committee members must have completed evaluations

                $completeness = $this->checkEvaluationCompleteness($id);
                Log::info('Evaluation completeness:', $completeness);

                if (! $completeness['is_complete']) {
                    $msg = 'Cannot create award: Not all evaluations are complete. '
                         . $completeness['members_completed'] . ' of ' . $completeness['total_members']
                         . ' committee members have finished evaluating all ' . $completeness['total_bids'] . ' bid(s).';
                    if (! empty($completeness['pending_members'])) {
                        $msg .= ' Pending: ' . implode(', ', $completeness['pending_members']) . '.';
                    }

                    DB::rollBack();
                    Log::warning('Evaluation incomplete, rolling back:', ['message' => $msg]);

                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => $msg,
                        ], 400);
                    }

                    return redirect()->back()->with('error', $msg);
                }


                // Check if any queued/active award exists for this tender.
                $existingActiveAward = TenderAward::where('TenderID', (int)$request->tender_id)
                    ->whereIn('AwardStatus', $this->blockingAwardStatuses())
                    ->orderByDesc('Id')
                    ->first();

                if ($existingActiveAward) {
                    return redirect()->back()->with(
                        'error',
                        'An award already exists for this tender with status "' . $existingActiveAward->AwardStatus .
                        '". Cancel it first before creating another award.'
                    );
                }

                // Ensure evaluator completion before allowing award:
                // every accepted active evaluator must either submit or be marked [SKIPPED].
                $pendingEvaluatorCount = DB::table('t_TenderCommitteeMembers as m')
                    ->where('m.TenderID', (int)$request->tender_id)
                    ->where('m.IsActive', 1)
                    ->where('m.Response', 1)
                    ->where(function ($q) {
                        $q->whereNull('m.reason')
                            ->orWhere(function ($inner) {
                                $inner->whereRaw("UPPER(LTRIM(RTRIM(m.reason))) NOT LIKE 'SKIPPED:%'")
                                    ->whereRaw("UPPER(LTRIM(RTRIM(m.reason))) NOT LIKE '[[]SKIPPED[]]%'");
                            });
                    })
                    ->where(function ($q) {
                        $q->whereNull('m.HasEvaluated')
                            ->orWhere('m.HasEvaluated', 0);
                    })
                    ->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('t_TenderCommitteeEvaluations as e')
                            ->whereColumn('e.TenderID', 'm.TenderID')
                            ->whereColumn('e.MemberID', 'm.Id');
                    })
                    ->count();

                if ($pendingEvaluatorCount > 0) {
                    return redirect()->back()->with(
                        'error',
                        'Evaluation is still pending for one or more evaluators. Complete evaluations or mark pending evaluators as skipped from the consolidation page.'
                    );
                }

                $user = Auth::user();
                $id = $request->tender_id;

                $awardData = [
                    'TenderID' => $id,
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
                    'AwardStatus' => 'Pending',
                    'CreatedBy' => $user->Id,
                    'ModifiedBy' => $user->Id,
                ];



                // Create award in Pending status
                $award = TenderAward::create($awardData);

                Log::info('TenderAward created:', ['award_id' => $award->Id, 'tender_id' => $award->TenderID]);

                // Log activity
                activity()
                    ->performedOn($award)
                    ->causedBy($user)
                    ->withProperties(['type' => 'tender', 'tender_id' => $id])
                    ->log('Created tender award (Pending) for tender ID: ' . $id);


                DB::commit();
                Log::info('Transaction committed successfully');

                // Check if it's an AJAX request
                if ($request->expectsJson() || $request->ajax()) {
                    Log::info('Returning JSON response');

                    return response()->json([
                        'success' => true,
                        'message' => 'Tender award created successfully.',
                        'award_id' => $award->Id,
                        'redirect' => route('bidscores.index', ['tender_id' => $id]),
                    ]);
                }

                Log::info('Redirecting to bidscores.index');

                return redirect()->route('bidscores.index', ['tender_id' => $id])
                    ->with('success', 'Tender award created successfully! The award is now pending approval.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('=== AWARD STORE EXCEPTION ===');
            Log::error('Exception message: ' . $e->getMessage());
            Log::error('Exception trace: ' . $e->getTraceAsString());
            Log::error('Exception file: ' . $e->getFile() . ':' . $e->getLine());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create award: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to create award: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Resolve incoming supplier reference to a valid t_Suppliers.Id.
     * Supports direct Supplier.Id, SupplierMaster.Id, and ThirdParty.Id inputs.
     */
    private function resolveWinningSupplierId($candidate, $tenderId = null): ?int
    {
        if ($candidate === null || $candidate === '') {
            return null;
        }

        $candidate = (int) $candidate;
        $tenderId = $tenderId !== null && $tenderId !== '' ? (int) $tenderId : null;

        // 1) Already a valid Supplier row ID.
        $direct = DB::table('t_Suppliers')
            ->where('Id', $candidate)
            ->whereNull('DeletedOn')
            ->value('Id');
        if ($direct) {
            return (int) $direct;
        }

        // 2) Prefer tender invitation mapping when available.
        if ($tenderId) {
            $invited = DB::table('t_TenderInvitations as ti')
                ->join('t_Suppliers as s', 's.Id', '=', 'ti.SupplierId')
                ->where('ti.TenderId', $tenderId)
                ->whereNull('ti.DeletedOn')
                ->whereNull('s.DeletedOn')
                ->where(function ($q) use ($candidate) {
                    $q->where('s.SupplierMasterId', $candidate)
                        ->orWhere('s.Id', $candidate);
                })
                ->orderByDesc('ti.InvitationID')
                ->value('s.Id');
            if ($invited) {
                return (int) $invited;
            }
        }

        // 3) Treat candidate as SupplierMaster.Id.
        $fromMaster = DB::table('t_Suppliers')
            ->where('SupplierMasterId', $candidate)
            ->whereNull('DeletedOn')
            ->orderByDesc('Active_Status')
            ->orderByDesc('Id')
            ->value('Id');
        if ($fromMaster) {
            return (int) $fromMaster;
        }

        // 4) Treat candidate as ThirdParty.Id -> SupplierMaster.Id -> Supplier.Id.
        $supplierMasterId = DB::table('t_SupplierMaster')
            ->where('ThirdPartyId', $candidate)
            ->value('Id');
        if ($supplierMasterId) {
            $fromThirdParty = DB::table('t_Suppliers')
                ->where('SupplierMasterId', (int) $supplierMasterId)
                ->whereNull('DeletedOn')
                ->orderByDesc('Active_Status')
                ->orderByDesc('Id')
                ->value('Id');
            if ($fromThirdParty) {
                return (int) $fromThirdParty;
            }
        }

        return null;
    }

    public function submitForApproval(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();
            $requestedType = strtolower((string) ($request->input('award_type') ?? $request->input('type') ?? ''));

            $award = null;
            $type = 'tender';
            $workflowService = $this->workflow;
            $statusEnum = \App\Enums\TenderAwardStatusEnum::SUBMITTED;
            $statusSubmitted = TenderAward::STATUS_SUBMITTED;
            $validStatuses = [TenderAward::STATUS_DRAFT, TenderAward::STATUS_PENDING];

            if ($requestedType === 'rfq') {
                $award = RFQAward::find($id);
                $type = 'rfq';
                $workflowService = $this->rfqWorkflow;
                $statusEnum = \App\Enums\RFQAwardStatusEnum::SUBMITTED;
                $statusSubmitted = RFQAward::STATUS_SUBMITTED;
                $validStatuses = [RFQAward::STATUS_PENDING];
            } elseif ($requestedType === 'tender') {
                $award = TenderAward::find($id);
            } else {
                // Backward-compatible fallback when type is not provided.
                $award = TenderAward::find($id);
                if (! $award) {
                    $award = RFQAward::find($id);
                    $type = 'rfq';
                    $workflowService = $this->rfqWorkflow;
                    $statusEnum = \App\Enums\RFQAwardStatusEnum::SUBMITTED;
                    $statusSubmitted = RFQAward::STATUS_SUBMITTED;
                    $validStatuses = [RFQAward::STATUS_PENDING];
                }
            }

            if (! $award) {
                return redirect()->back()->with('error', 'Award not found.');
            }

            // Prevent submitting when another queued/active award exists for the same source.
            if ($type === 'tender') {
                $duplicateQueuedAward = TenderAward::where('TenderID', (int) $award->TenderID)
                    ->where('Id', '<>', (int) $award->Id)
                    ->whereIn('AwardStatus', $this->blockingAwardStatuses())
                    ->exists();

                if ($duplicateQueuedAward) {
                    return redirect()->back()->with(
                        'error',
                        'Another award for this tender is already in queue. Cancel the existing queued award before submitting this one.'
                    );
                }
            } else {
                $duplicateQueuedAward = RFQAward::where('RFQId', (int) $award->RFQId)
                    ->where('Id', '<>', (int) $award->Id)
                    ->whereIn('AwardStatus', $this->blockingAwardStatuses())
                    ->exists();

                if ($duplicateQueuedAward) {
                    return redirect()->back()->with(
                        'error',
                        'Another award for this RFQ is already in queue. Cancel the existing queued award before submitting this one.'
                    );
                }
            }

            // Validation: Must be in Draft or Pending status
            if (! in_array($award->AwardStatus, $validStatuses)) {
                return redirect()->back()->with('error', 'Only draft or pending awards can be submitted for approval.');
            }

            // Submit to workflow
            $submitted = $workflowService->submit(
                $award,
                $user,
                $statusEnum,
                ucfirst($type) . ' Award submitted for approval'
            );

            if (! $submitted) {
                throw new \Exception('Failed to submit award to workflow');
            }


            $award->update([
                'AwardStatus' => $statusSubmitted,
                'ModifiedBy' => $user->Id,
                'ModifiedOn' => now(),
            ]);

            DB::commit();

            activity()
                ->performedOn($award)
                ->causedBy($user)
                ->withProperties(['action' => 'submit_for_approval', 'type' => $type])
                ->log('Submitted ' . $type . ' award for approval: Award ID ' . $award->Id);

            return redirect()->route('procawards.index')
                ->with('success', ucfirst($type) . ' Award submitted for approval successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Award Submit Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()->with('error', 'Failed to submit award: ' . $e->getMessage());
        }
    }

    /**
     * Approve award (Unified for Tender and RFQ)
     */
    public function approve(Request $request, $id = null)
    {
        $request->validate([
             'approval_remarks' => 'nullable|string|max:1000',
             'remarks' => 'nullable|string|max:1000',
        ]);

        $remarks = $request->approval_remarks ?? $request->remarks;
        $awardId = $request->award_id ?? $id;

        if (! $awardId) {
            return redirect()->back()->with('error', 'Award ID is required.');
        }

        try {
            DB::beginTransaction();
            $user = Auth::user();

            // Check for explicit type
            $requestedType = $request->input('type') ?? $request->input('award_type');
            $award = null;
            $type = null;

            if ($requestedType === 'rfq') {
                $award = RFQAward::find($awardId);
                $type = 'rfq';
                $workflowService = $this->rfqWorkflow;
                $statusEnum = \App\Enums\RFQAwardStatusEnum::APPROVED;
            } elseif ($requestedType === 'tender') {
                $award = TenderAward::find($awardId);
                $type = 'tender';
                $workflowService = $this->workflow;
                $statusEnum = \App\Enums\TenderAwardStatusEnum::APPROVED;
            } else {
                // Fallback: Try Tender then RFQ
                $award = TenderAward::find($awardId);
                if ($award) {
                    $type = 'tender';
                    $workflowService = $this->workflow;
                    $statusEnum = \App\Enums\TenderAwardStatusEnum::APPROVED;
                } else {
                    $award = RFQAward::find($awardId);
                    $type = 'rfq';
                    $workflowService = $this->rfqWorkflow;
                    $statusEnum = \App\Enums\RFQAwardStatusEnum::APPROVED;
                }
            }

            $statusColumn = 'AwardStatus';

            if (! $award) {
                return redirect()->back()->with('error', 'Award not found.');
            }

            Log::info("Starting unified award approval", [
                'type' => $type,
                'award_id' => $award->Id,
                'user_id' => $user->Id,
                'current_status' => $award->AwardStatus,
            ]);

            // Check if user can approve
            $canApprove = $workflowService->canApproveModel($award, $user);

            Log::info("Approval permission check result", [
                'can_approve' => $canApprove,
                'user_id' => $user->Id,
                'creator_id' => $award->CreatedBy,
                'is_maker' => $user->Id === $award->CreatedBy,
            ]);

            if (! $canApprove) {
                // Check For Exemptions (Maker-Checker)
                $exemptions = config('workflow.maker_checker_exempt', []);
                $isExempt = in_array($type . '_award', $exemptions);

                Log::info("Maker-Checker Exemption Check", [
                    'exemptions' => $exemptions,
                    'target' => $type . '_award',
                    'is_exempt' => $isExempt,
                ]);

                if (! $isExempt) {
                    Log::warning("User not authorized to approve award", ['id' => $award->Id, 'type' => $type]);

                    return redirect()->back()->with('error', 'You are not authorized to approve this award at this stage.');
                }
            }

            // Validate award is in submitted/pending/under review status
            $validStatuses = $type === 'tender'
                ? [TenderAward::STATUS_SUBMITTED, TenderAward::STATUS_UNDER_REVIEW, TenderAward::STATUS_PENDING]
                : [RFQAward::STATUS_SUBMITTED, RFQAward::STATUS_UNDER_REVIEW, RFQAward::STATUS_PENDING];

            if (! in_array($award->AwardStatus, $validStatuses)) {
                return redirect()->back()->with('error', 'Only pending/submitted awards can be approved.');
            }

            // Use workflow to approve
            $approved = $workflowService->approve(
                $award,
                $user,
                $statusEnum,
                $remarks,
                $statusColumn
            );

            if (! $approved) {
                throw new \Exception('Workflow approval failed');
            }

            Log::info("Workflow approval successful", ['id' => $award->Id, 'type' => $type]);

            // Update award status model-side
            $award->refresh();
            if ($type === 'tender') {
                $award->update([
                    'AwardStatus' => 'Approved',
                    'ApprovedBy' => $user->Id,
                    'ApprovedOn' => now(),
                    'ModifiedBy' => $user->Id,
                    'ModifiedOn' => now(),
                ]);

                $this->deactivateTenderCommittee((int) $award->TenderID, (int) $user->Id);

                // Update Tender Status
                if ($award->tender) {
                    $award->tender->update(['Status' => \App\Enums\TenderStatusEnum::Awarded->value]);
                }
            } else {
                $award->approve($user, $remarks);
                // Also update the RFQ status to 'Awarded' (Aw)
                // Use 'Aw' as added to CodeDetailSeeder
                if ($award->rfq) {
                    $award->rfq->update([
                        'Status' => 'Aw',
                        'ModifiedBy' => $user->Id,
                        'ModifiedOn' => now(),
                    ]);
                }
            }




            DB::commit();


            try {
                // 1. Notify Creator
                $creator = \App\Models\Auth\User::find($award->CreatedBy);
                if ($creator && $creator->Email) {
                    try {
                        $refNumber = $type === 'tender' ? ($award->tender->TenderNo ?? 'N/A') : ($award->rfq->RFQNumber ?? 'N/A');
                        $subject = ucfirst($type) . ' Award Approved: ' . $refNumber;
                        $body = "<p>The " . strtoupper($type) . " award for <strong>" . $refNumber . "</strong> has been approved.</p>";
                        $to = [[$creator->Name => $creator->Email]];

                        $service = \App\Services\CRMEmailService::createRaw(
                            Auth::user(),
                            $subject,
                            $body,
                            $to,
                            null,
                            null,
                            [],
                            [],
                            \App\Enums\EmailPriorityEnum::Normal
                        );
                        $service->send(true);
                    } catch (\Throwable $e) {
                        Log::error("Failed to send approval email: " . $e->getMessage());
                    }
                }

                // 2. Notify Suppliers
                if ($type === 'tender') {
                    $this->notifySuccessfulBidders($award);

                    if ($award->NotifyUnsuccessfulBidders) {
                        $this->notifyUnsuccessfulBidders($award);
                    }
                } else {
                    $this->notifySuccessfulRFQBidders($award);
                }
            } catch (\Throwable $e) {
                // Log major notification failure but do NOT fail the request as the transaction is committed
                Log::error("Post-approval notification error: " . $e->getMessage());

                return redirect()->route('procawards.index')
                    ->with('success', ucfirst($type) . ' Award approved successfully, but some notifications may not have been sent.');
            }

            activity()
                ->performedOn($award)
                ->causedBy($user)
                ->withProperties([
                    'action' => 'approve',
                    'type' => $type,
                    'approval_remarks' => $remarks,
                ])
                ->log(ucfirst($type) . ' Award approved: Award ID ' . $award->Id);



            return redirect()->route('procawards.index')
                ->with('success', ucfirst($type) . ' Award approved successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("--- APPROVE AWARD ERROR --- " . $th->getMessage());
            Log::error($th->getTraceAsString());

            return redirect()->back()->with('error', 'Failed to approve award: ' . $th->getMessage());
        }
    }

    public function reject(Request $request, $id = null)
    {
        $request->validate([
              'reason' => 'required|string|max:1000',
         ]);

        $awardId = $request->award_id ?? $id;

        if (! $awardId) {
            return redirect()->back()->with('error', 'Award ID is required.');
        }

        try {
            DB::beginTransaction();
            $user = Auth::user();

            // Check for explicit type
            $requestedType = $request->input('type') ?? $request->input('award_type');
            $award = null;
            $type = null;

            if ($requestedType === 'rfq') {
                $award = RFQAward::find($awardId);
                $type = 'rfq';
                $workflowService = $this->rfqWorkflow;
                $statusEnum = \App\Enums\RFQAwardStatusEnum::REJECTED;
            } elseif ($requestedType === 'tender') {
                $award = TenderAward::find($awardId);
                $type = 'tender';
                $workflowService = $this->workflow;
                $statusEnum = \App\Enums\TenderAwardStatusEnum::REJECTED;
            } else {
                // Fallback
                $award = TenderAward::find($awardId);
                if ($award) {
                    $type = 'tender';
                    $workflowService = $this->workflow;
                    $statusEnum = \App\Enums\TenderAwardStatusEnum::REJECTED;
                } else {
                    $award = RFQAward::find($awardId);
                    $type = 'rfq';
                    $workflowService = $this->rfqWorkflow;
                    $statusEnum = \App\Enums\RFQAwardStatusEnum::REJECTED;
                }
            }

            $statusColumn = 'AwardStatus';

            if (! $award) {
                return redirect()->back()->with('error', 'Award not found.');
            }

            Log::info("Starting unified award rejection", [
                'type' => $type,
                'award_id' => $award->Id,
                'user_id' => $user->Id,
            ]);

            // Check if user can approve (same perm for reject)
            if (! $workflowService->canApproveModel($award, $user)) {
                return redirect()->back()->with('error', 'You are not authorized to reject this award.');
            }

            // Validate status
            $validStatuses = $type === 'tender'
               ? [TenderAward::STATUS_SUBMITTED, TenderAward::STATUS_UNDER_REVIEW, TenderAward::STATUS_PENDING]
               : [RFQAward::STATUS_SUBMITTED, RFQAward::STATUS_UNDER_REVIEW, RFQAward::STATUS_PENDING];

            if (! in_array($award->AwardStatus, $validStatuses)) {
                return redirect()->back()->with('error', 'Only pending/submitted awards can be rejected.');
            }

            // Use workflow to reject
            $rejected = $workflowService->reject(
                $award,
                $user,
                $statusEnum,
                $request->reason,
                $statusColumn
            );

            if (! $rejected) {
                throw new \Exception('Workflow rejection failed');
            }

            // Update award status
            $award->refresh();
            if ($type === 'tender') {
                $award->update([
                    'AwardStatus' => 'Rejected',
                    'RejectionReason' => $request->reason,
                    'ModifiedBy' => $user->Id,
                    'ModifiedOn' => now(),
                ]);
                $this->deactivateTenderCommittee((int) $award->TenderID, (int) $user->Id);
            } else {
                $award->reject($user, $request->reason);

                $this->deactivateRfqCommittee((int) $award->RFQID, (int) $user->Id);

                // Notify creator for RFQ
                $creator = \App\Models\Auth\User::find($award->CreatedBy);
                if ($creator && $creator->Email) {
                    try {
                        $rfqNumber = $award->rfq->RFQNumber ?? 'N/A';
                        $subject = 'RFQ Award Rejected: ' . $rfqNumber;
                        $body = "<p>The RFQ award for <strong>" . $rfqNumber . "</strong> has been rejected.</p>";
                        $body .= "<p><strong>Reason:</strong> " . e($request->reason) . "</p>";
                        $to = [[$creator->Name => $creator->Email]];

                        $service = \App\Services\CRMEmailService::createRaw(
                            Auth::user(),
                            $subject,
                            $body,
                            $to,
                            null,
                            null,
                            [],
                            [],
                            \App\Enums\EmailPriorityEnum::Normal
                        );
                        $service->send(true);
                    } catch (\Exception $e) {
                        Log::error("Failed to send RFQ rejection email: " . $e->getMessage());
                    }
                }
            }

            activity()
                ->performedOn($award)
                ->causedBy($user)
                ->withProperties([
                    'action' => 'reject',
                    'type' => $type,
                    'rejection_reason' => $request->reason,
                ])
                ->log(ucfirst($type) . ' Award rejected: Award ID ' . $award->Id);

            DB::commit();

            return redirect()->route('procawards.index')
                ->with('success', ucfirst($type) . ' Award rejected successfully.');
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
                    'currency' => $bid->Currency,
                ];
            });

        // Shared computation to ensure parity with consolidation page
        $sections = $this->getTenderSections($tenderId);
        $computed = TenderScoringService::compute((int)$tenderId);

        $consolidatedScores = [];
        foreach ($bidders as $bidder) {
            $sid = (int)$bidder['id'];
            $entry = $computed[$sid] ?? null;
            if (! $entry) {
                continue;
            }

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
            ->filter(fn ($ts) => $ts->sections)
            ->map(function ($ts) use ($tenderId) {
                $section = $ts->sections;

                // Retrieve tender-specific criteria rows to get MaxScore
                $tcRows = \App\Models\Procurement\TenderCriteria::where('TenderID', $tenderId)
                    ->where('SectionID', $section->Id)
                    ->where('IsActive', true)
                    ->whereNull('DeletedOn')
                    ->with('criteria')
                    ->get();

                $criteria = $tcRows->map(function ($tc) {
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
            'financial_score' => $this->getFinancialScore($sectionScores),
        ];
    }

    /**
     * Calculate average score for a section across all evaluators (same as BidScoreConsolidationController)
     */
    protected function calculateSectionScoreForAward($section, $evaluations, $tenderId, int $supplierId)
    {
        // Keep for compatibility; delegate to member-based helper and average
        $supplierGroup = $evaluations->get($supplierId) ?? collect();
        if ($supplierGroup->isEmpty()) {
            return 0.0;
        }
        $sum = 0.0;
        $cnt = 0;
        foreach ($supplierGroup as $memberData) {
            $sum += $this->memberSectionPercent($memberData, $section);
            $cnt++;
        }

        return $cnt > 0 ? $sum / $cnt : 0.0;
    }

    private function memberSectionPercent($memberData, array $section): float
    {
        $sumScore = 0.0;
        $sumMax = 0.0;
        $sectionRows = optional($memberData)->get($section['id']) ?? collect();
        foreach ($section['criteria'] as $criteria) {
            $rows = optional($sectionRows)->get($criteria['id']) ?? collect();
            if ($rows->isEmpty()) {
                continue;
            }
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
        $technicalSection = collect($sectionScores)->first(function ($section) {
            return stripos($section['section_name'], 'technical') !== false;
        });

        return $technicalSection ? $technicalSection['score'] : null;
    }

    /**
     * Extract financial score from section scores
     */
    protected function getFinancialScore($sectionScores)
    {
        $financialSection = collect($sectionScores)->first(function ($section) {
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

        // Block if there is any queued/active award.
        $hasActive = TenderAward::where('TenderID', $tenderId)
            ->whereIn('AwardStatus', $this->blockingAwardStatuses())
            ->exists();
        if ($hasActive) {
            return redirect()->route('awards.tender', $tenderId)
                ->with('info', 'This tender already has an award in queue. Cancel it first before creating another award.');
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
     * Award statuses that block creating/submitting another award for the same tender.
     */
    private function blockingAwardStatuses(): array
    {
        return [
            TenderAward::STATUS_DRAFT,
            TenderAward::STATUS_PENDING,
            TenderAward::STATUS_SUBMITTED,
            TenderAward::STATUS_UNDER_REVIEW,
            TenderAward::STATUS_APPROVED,
        ];
    }

    protected function deactivateTenderCommittee(int $tenderId, int $actorId): void
    {
        $now = now();

        $committeeIds = TenderCommittee::query()
            ->where('IsActive', true)
            ->where(function ($query) use ($tenderId) {
                $query->where('TenderID', $tenderId)
                    ->orWhere(function ($subQuery) use ($tenderId) {
                        $subQuery->where('CommitteeType', 'tender')
                            ->where('ReferenceId', $tenderId);
                    });
            })
            ->pluck('Id')
            ->values();

        if ($committeeIds->isEmpty()) {
            return;
        }

        TenderCommittee::query()
            ->whereIn('Id', $committeeIds->all())
            ->update([
                'IsActive' => false,
                'ModifiedBy' => $actorId,
                'ModifiedOn' => $now,
            ]);

        TenderCommitteeMember::query()
            ->whereIn('CommitteeID', $committeeIds->all())
            ->where('IsActive', true)
            ->update([
                'IsActive' => false,
                'ModifiedBy' => $actorId,
                'ModifiedOn' => $now,
            ]);
    }

    protected function notifySuccessfulBidders($award)
    {
        try {

            $tenderTitle = $award->tender->Title ?? 'Tender';
            $tenderNo = $award->tender->TenderNo;

            // Get supplier contact info
            $winningSupplier = \App\Models\ThirdParies\Supplier::find($award->WinningSupplierID);
            if (! $winningSupplier) {
                Log::warning("Winning supplier not found for award {$award->Id}");

                return;
            }

            $thirdParty = $winningSupplier->supplierMaster?->thirdParty;

            if (! $thirdParty) {
                Log::warning("ThirdParty record not found for winning supplier ID {$award->SupplierID}");

                return;
            }

            $contactUser = $thirdParty->users ? $thirdParty->users->first() : null;
            $email = $contactUser ? $contactUser->Email : ($thirdParty->Email ?? null);
            $name = $contactUser ? $contactUser->Name : ($thirdParty->ThirdPartyName ?? 'Valued Supplier');

            if ($email) {
                $subject = "Award Notification - {$tenderTitle} ({$tenderNo})";
                $body = "Dear {$name},<br><br>" .
                        "We are pleased to inform you that your bid for the tender <strong>{$tenderTitle} ({$tenderNo})</strong> has been successful.<br><br>" .
                        "We will be in touch shortly with further details regarding the contract and next steps.<br><br>" .
                        "Congratulations and we look forward to working with you.<br><br>" .
                        "Sincerely,<br>" .
                        "Procurement Department<br>" .
                        config('app.name');

                $to = [['Name' => $email]]; // CRMEmailService expects [['Name' => 'Email']] or [['Name' => 'Email']] logic

                $service = \App\Services\CRMEmailService::createRaw(
                    Auth::user(),
                    $subject,
                    $body,
                    $to,
                    \App\Models\ThirdParty\ThirdParties::class, // Correct Morph Class
                    (string)$thirdParty->Id,
                    [],
                    [],
                    \App\Enums\EmailPriorityEnum::Important
                );
                $service->send(true);
                Log::info("Award notification sent to winner {$email} for tender {$tenderNo}");
            } else {
                Log::warning("No email found for winning supplier ID {$award->WinningSupplierID}");
            }

        } catch (\Throwable $e) {
            Log::error("Failed to notify successful bidder: " . $e->getMessage());
        }
    }

    /**
     * Send notifications to unsuccessful bidders
     */
    protected function deactivateRfqCommittee(int $rfqId, int $actorId): void
    {
        $now = now();

        $committeeIds = RFQCommittee::query()
            ->where('RFQID', $rfqId)
            ->where('IsActive', true)
            ->pluck('Id')
            ->values();

        if ($committeeIds->isEmpty()) {
            return;
        }

        RFQCommittee::query()
            ->whereIn('Id', $committeeIds->all())
            ->update([
                'IsActive' => false,
                'ModifiedBy' => $actorId,
                'ModifiedOn' => $now,
            ]);

        RFQCommitteeMember::query()
            ->whereIn('CommitteeID', $committeeIds->all())
            ->where('IsActive', true)
            ->update([
                'IsActive' => false,
                'ModifiedBy' => $actorId,
                'ModifiedOn' => $now,
            ]);
    }

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

                if (! $supplier || ! $supplier->supplierMaster || ! $supplier->supplierMaster->thirdParty) {
                    continue;
                }

                $thirdParty = $supplier->supplierMaster->thirdParty; // SupplierMaster -> ThirdParty

                // Try to find a user/contact to email
                $contactUser = $thirdParty->users ? $thirdParty->users->first() : null; // Get first user as primary contact
                $email = $contactUser ? $contactUser->Email : ($thirdParty->Email ?? null);
                $name = $contactUser ? $contactUser->Name : ($thirdParty->ThirdPartyName ?? 'Supplier');

                if ($email) {
                    // Send Regret Letter via CRMEmailService
                    $subject = "Regret Letter - {$tenderTitle} ({$tenderNo})";
                    $body = "Dear {$name},<br><br>" .
                            "Thank you for participating in the tender <strong>{$tenderTitle} ({$tenderNo})</strong>.<br><br>" .
                            "We regret to inform you that your bid was not successful on this occasion. The contract has been awarded to another bidder.<br><br>" .
                            "We appreciate the time and effort you put into your submission and encourage you to participate in our future tenders.<br><br>" .
                            "Sincerely,<br>" .
                            "Procurement Department<br>" .
                            config('app.name');

                    try {
                        $to = [['Name' => $email]];

                        $service = \App\Services\CRMEmailService::createRaw(
                            Auth::user(),
                            $subject,
                            $body,
                            $to,
                            \App\Models\ThirdParty\ThirdParties::class, // Correct Morph Class
                            (string)$thirdParty->Id,
                            [],
                            [],
                            \App\Enums\EmailPriorityEnum::Normal
                        );
                        $service->send(true); // Send immediately

                        Log::info("Regret email sent to {$email} for tender {$tenderNo}");
                    } catch (\Exception $e) {
                        Log::error("Failed to send email to {$email}: " . $e->getMessage());
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error("Failed to notify unsuccessful bidders: " . $e->getMessage());
        }
    }

    /**
     * Notify the successful bidder for RFQ
     */
    protected function notifySuccessfulRFQBidders($award)
    {
        try {
            $rfqTitle = $award->rfq->Subject ?? ($award->rfq->Comments ?? 'RFQ Award');
            $rfqNo = $award->rfq->RFQNumber;

            // Get supplier contact info
            $winningSupplier = \App\Models\ThirdParies\Supplier::find($award->SupplierId);
            if (! $winningSupplier) {
                Log::warning("Winning supplier not found for RFQ award {$award->Id}");

                return;
            }

            $thirdParty = $winningSupplier->supplierMaster ? $winningSupplier->supplierMaster->thirdParty : null;

            if (! $thirdParty) {
                Log::warning("ThirdParty record not found for winning supplier ID {$award->SupplierId}");

                return;
            }

            $contactUser = $thirdParty->users ? $thirdParty->users->first() : null;
            $email = $contactUser ? $contactUser->Email : ($thirdParty->Email ?? null);
            $name = $contactUser ? $contactUser->Name : ($thirdParty->ThirdPartyName ?? 'Valued Supplier');

            if ($email) {
                $subject = "Award Notification - {$rfqTitle} ({$rfqNo})";
                $body = "Dear {$name},<br><br>" .
                        "We are pleased to inform you that your quotation for <strong>{$rfqTitle} ({$rfqNo})</strong> has been successful.<br><br>" .
                        "We will be in touch shortly with further details regarding the next steps.<br><br>" .
                        "Congratulations and we look forward to working with you.<br><br>" .
                        "Sincerely,<br>" .
                        "Procurement Department<br>" .
                        config('app.name');

                $to = [['Name' => $email]];

                $service = \App\Services\CRMEmailService::createRaw(
                    Auth::user(),
                    $subject,
                    $body,
                    $to,
                    \App\Models\ThirdParty\ThirdParties::class, // Correct Morph Class
                    (string)$thirdParty->Id,
                    [],
                    [],
                    \App\Enums\EmailPriorityEnum::Important
                );
                $service->send(true);
                Log::info("RFQ Award notification sent to winner {$email} for RFQ {$rfqNo}");
            } else {
                Log::warning("No email found for winning supplier ID {$award->SupplierId}");
            }

        } catch (\Throwable $e) {
            Log::error("Failed to notify successful RFQ bidder: " . $e->getMessage());
        }
    }

    /**
     * Check if all committee members have completed evaluations for all bids of a tender.
     *
     * Returns an array with:
     *   - is_complete (bool)
     *   - total_members, members_completed
     *   - total_bids
     *   - pending_members (array of names)
     */
    // Optimized version:
    protected function checkEvaluationCompleteness(int $tenderId): array
    {
        $tender = Tender::find($tenderId);
        if (! $tender) {
            return $this->buildResponse(false, 0, 0, 0, ['Tender not found']);
        }

        $members = TenderCommitteeMember::where(function ($q) use ($tenderId) {
            $q->where('TenderID', $tenderId)
              ->orWhereHas('committee', function ($cq) use ($tenderId) {
                  $cq->where('CommitteeType', 'tender')
                     ->where('ReferenceId', $tenderId);
              });
        })
            ->where('IsActive', 1)
            ->where(function ($q) {
                $q->whereNull('Response')->orWhere('Response', 1);
            })
            ->with('user')
            ->get();



        $totalMembers = $members->count();
        if ($totalMembers === 0) {
            return $this->buildResponse(false, 0, 0, 0, ['No committee members assigned']);
        }

        $responsiveBids = BidSubmission::where('TenderRef', $tender->TenderNo)
            ->where('IsResponsive', true)
            ->whereIn('BidStatus', ['responsive', 'evaluated'])
            ->get();

        $totalBids = $responsiveBids->count();
        if ($totalBids === 0) {
            return $this->buildResponse(false, $totalMembers, 0, 0, ['No responsive bids found']);
        }

        $criteriaIds = DB::table('t_TenderCriteria')
            ->where('TenderID', $tenderId)
            ->where('IsActive', true)
            ->pluck('CriteriaID');

        $totalCriteria = $criteriaIds->count();
        if ($totalCriteria === 0) {
            return $this->buildResponse(false, $totalMembers, 0, $totalBids, ['No evaluation criteria defined']);
        }

        $supplierIds = $responsiveBids->pluck('SupplierId')->unique()->values();
        $requiredCombinations = $supplierIds->count() * $totalCriteria;

        $memberIds = $members->pluck('id')->toArray();

        // Batch fetch all evaluations to avoid N+1
        $allEvaluations = TenderCommitteeEvaluation::where('TenderID', $tenderId)
            ->whereIn('MemberID', $members->pluck('id'))
            ->whereIn('SupplierId', $supplierIds)
            ->whereIn('CriteriaID', $criteriaIds)
            ->select('MemberID', 'SupplierId', 'CriteriaID')
            ->distinct()
            ->get()
            ->groupBy('MemberID');


        $pendingMembers = [];
        $membersCompleted = 0;

        foreach ($members as $member) {
            $memberEvaluations = $allEvaluations->get($member->id, collect());
            $actualCombinations = $memberEvaluations->count();

            if ($actualCombinations >= $requiredCombinations) {
                $membersCompleted++;
            } else {
                $memberName = $member->user->Name ?? ('Member #' . $member->id);
                $missing = $requiredCombinations - $actualCombinations;
                $pendingMembers[] = "$memberName ($missing evaluations pending)";
            }
        }

        return [
            'is_complete' => $membersCompleted >= $totalMembers,
            'total_members' => $totalMembers,
            'members_completed' => $membersCompleted,
            'total_bids' => $totalBids,
            'pending_members' => $pendingMembers,
        ];
    }

    /**
     * Check if all committee members have completed evaluations for all supplier responses of an RFQ.
     *
     * Returns the same structure as checkEvaluationCompleteness().
     */
    protected function checkRFQEvaluationCompleteness(int $rfqId): array
    {
        $rfq = RFQ::find($rfqId);
        if (! $rfq) {
            return $this->buildResponse(false, 0, 0, 0, ['RFQ not found']);
        }

        // 1. Get all active committee members with user relationship
        $members = RFQCommitteeMember::where('RFQID', $rfqId)
            ->where('IsActive', 1)
            ->where(function ($q) {
                $q->whereNull('Response')->orWhere('Response', 1);
            })
            ->with('user')
            ->get();

        $totalMembers = $members->count();
        if ($totalMembers === 0) {
            return $this->buildResponse(false, 0, 0, 0, ['No committee members assigned']);
        }

        // 2. Get suppliers who have responded to this RFQ
        $supplierIds = RFQResponse::where('RFQId', $rfqId)
            ->pluck('SupplierId')
            ->unique()
            ->values();

        $totalBids = $supplierIds->count();
        if ($totalBids === 0) {
            return $this->buildResponse(false, $totalMembers, 0, 0, ['No supplier responses found']);
        }

        // 3. Get active criteria IDs for this RFQ
        $criteriaIds = RFQCriteria::where('RFQID', $rfqId)
            ->where('IsActive', true)
            ->pluck('Id'); // Get actual IDs, not just count

        $totalCriteria = $criteriaIds->count();
        if ($totalCriteria === 0) {
            return $this->buildResponse(false, $totalMembers, 0, $totalBids, ['No evaluation criteria defined']);
        }

        $requiredCombinations = $totalCriteria * $totalBids;

        // 4. Batch fetch all evaluations to avoid N+1
        $memberUserCodes = $members->pluck('UserID')->unique();

        $evaluationsByUser = RFQEvaluation::where('RFQId', $rfqId)
            ->whereIn('UserCode', $memberUserCodes)
            ->pluck('Id', 'UserCode'); // Map UserCode => EvaluationId

        // Get all supplier response evaluations grouped by evaluation ID
        $allSupplierEvaluations = RFQSupplierResponseEvaluation::whereIn('RFQEvaluationId', $evaluationsByUser->values())
            ->whereIn('SupplierId', $supplierIds)
            ->whereIn('CriteriaId', $criteriaIds) // Validate against active criteria
            ->select('RFQEvaluationId', 'SupplierId', 'CriteriaId')
            ->distinct()
            ->get()
            ->groupBy('RFQEvaluationId');

        $pendingMembers = [];
        $membersCompleted = 0;

        foreach ($members as $member) {
            $evaluationId = $evaluationsByUser->get($member->UserID);

            if (! $evaluationId) {
                $memberName = $member->user->Name ?? $member->committee_member_name ?? ('Member #' . $member->Id);
                $pendingMembers[] = "$memberName ({$requiredCombinations} evaluations pending)";

                continue;
            }

            // Check if member has evaluated ALL criteria for ALL suppliers
            $memberEvaluations = $allSupplierEvaluations->get($evaluationId, collect());
            $actualCombinations = $memberEvaluations->count();

            if ($actualCombinations >= $requiredCombinations) {
                $membersCompleted++;
            } else {
                $memberName = $member->user->Name ?? $member->committee_member_name ?? ('Member #' . $member->Id);
                $missing = $requiredCombinations - $actualCombinations;
                $pendingMembers[] = "$memberName ($missing evaluations pending)";
            }
        }

        return [
            'is_complete' => $membersCompleted >= $totalMembers,
            'total_members' => $totalMembers,
            'members_completed' => $membersCompleted,
            'total_bids' => $totalBids,
            'pending_members' => $pendingMembers,
        ];
    }

    private function buildResponse(bool $isComplete, int $totalMembers, int $membersCompleted, int $totalBids, array $pendingMembers): array
    {
        return [
            'is_complete' => $isComplete,
            'total_members' => $totalMembers,
            'members_completed' => $membersCompleted,
            'total_bids' => $totalBids,
            'pending_members' => $pendingMembers,
        ];
    }
}
