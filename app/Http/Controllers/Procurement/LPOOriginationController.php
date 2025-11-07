<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\Order;
use App\Models\Procurement\OrderLines;
use App\Models\Procurement\PlanLineItem;
use App\Models\Procurement\TenderAward;
use App\Models\ThirdParty\Supplier;
use App\Services\Procurement\Orders\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LPOOriginationController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display the unified LPO origination dashboard
     */
    public function index()
    {
        try {
            // Get counts for each origination type
            $contractBasedCount = $this->getContractBasedLPOsCount();
            $awardBasedCount = $this->getAwardBasedLPOsCount();
            $directProcurementCount = $this->getDirectProcurementLPOsCount();

            // Recent LPOs by type - using more defensive approach
            $recentContractLPOs = collect(); // Empty collection for now
            $recentAwardLPOs = collect(); // Empty collection for now
            $recentDirectLPOs = collect(); // Empty collection for now

            // Try to get recent LPOs safely
            try {
                $recentContractLPOs = Order::where('OriginationType', 'contract')
                    ->latest('CreatedOn')
                    ->take(5)
                    ->get();
            } catch (\Exception $e) {
                Log::warning('Failed to load recent contract LPOs: ' . $e->getMessage());
            }

            try {
                $recentAwardLPOs = Order::where('OriginationType', 'award')
                    ->latest('CreatedOn')
                    ->take(5)
                    ->get();
            } catch (\Exception $e) {
                Log::warning('Failed to load recent award LPOs: ' . $e->getMessage());
            }

            try {
                $recentDirectLPOs = Order::where('OriginationType', 'direct_procurement')
                    ->latest('CreatedOn')
                    ->take(5)
                    ->get();
            } catch (\Exception $e) {
                Log::warning('Failed to load recent direct LPOs: ' . $e->getMessage());
            }

            return view('procurement.lpo.origination.index', compact(
                'contractBasedCount',
                'awardBasedCount',
                'directProcurementCount',
                'recentContractLPOs',
                'recentAwardLPOs',
                'recentDirectLPOs'
            ));
        } catch (\Exception $e) {
            Log::error('LPO Origination Dashboard Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load LPO origination dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Show contract-based LPO creation options
     */
    public function showContractBasedOptions()
    {
        try {
            // Get active contracts available for LPO creation
            $activeContracts = TenderAward::where('ContractStatus', 'Executed')
                ->whereHas('tender')
                ->with(['tender', 'winningSupplier'])
                ->orderBy('ContractApprovedOn', 'desc')
                ->paginate(15);

            return view('procurement.lpo.origination.contract-based', compact('activeContracts'));
        } catch (\Exception $e) {
            Log::error('Contract-based LPO Options Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load contract options: ' . $e->getMessage());
        }
    }

    /**
     * Show award-based LPO creation options
     */
    public function showAwardBasedOptions()
    {
        try {
            // Get approved awards that don't require contracts (for direct LPO)
            $availableAwards = TenderAward::where('is_approved', true)
                ->where(function ($query) {
                    $query->whereNull('ContractStatus')
                        ->orWhere('ContractStatus', '!=', 'Executed');
                })
                ->whereHas('tender')
                ->with(['tender', 'winningSupplier'])
                ->orderBy('ApprovedOn', 'desc')
                ->paginate(15);

            return view('procurement.lpo.origination.award-based', compact('availableAwards'));
        } catch (\Exception $e) {
            Log::error('Award-based LPO Options Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load award options: ' . $e->getMessage());
        }
    }

    /**
     * Show direct procurement LPO creation options
     */
    public function showDirectProcurementOptions()
    {
        try {
            // Get approved procurement plans with direct procurement items
            $directProcurementPlans = ConsolidatedProcurementPlan::where('Status', 'Approved')
                ->whereHas('planLineItems', function ($query) {
                    $query->whereHas('procurementMode', function ($subQuery) {
                        $subQuery->where('Description', 'LIKE', '%Direct%')
                            ->orWhere('Description', 'LIKE', '%direct%');
                    })
                        ->where('ExecutionStatus', 'Pending');
                })
                ->with(['planLineItems' => function ($query) {
                    $query->whereHas('procurementMode', function ($subQuery) {
                        $subQuery->where('Description', 'LIKE', '%Direct%')
                            ->orWhere('Description', 'LIKE', '%direct%');
                    })
                        ->where('ExecutionStatus', 'Pending')
                        ->with(['item', 'procurementMode']);
                }])
                ->orderBy('ApprovedOn', 'desc')
                ->paginate(15);

            return view('procurement.lpo.origination.direct-procurement', compact('directProcurementPlans'));
        } catch (\Exception $e) {
            Log::error('Direct Procurement LPO Options Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load direct procurement options: ' . $e->getMessage());
        }
    }

    /**
     * Create LPO from contract
     */
    public function createFromContract(Request $request, $contractId)
    {
        try {
            $contract = TenderAward::with(['tender', 'winningSupplier'])
                ->findOrFail($contractId);

            if ($contract->ContractStatus !== 'Executed') {
                return redirect()->back()->with('error', 'Only executed contracts can be used for LPO creation.');
            }

            // Get suppliers for this contract (primary is the winning supplier)
            $suppliers = collect([$contract->winningSupplier]);

            // Pre-fill LPO data from contract
            $lpoData = [
                'origination_type' => 'contract',
                'contract' => $contract,
                'supplier' => $contract->winningSupplier,
                'contract_ref' => $contract->ContractRef,
                'contract_value' => $contract->ContractValue,
                'delivery_terms' => $contract->DeliveryTerms,
                'payment_terms' => $contract->PaymentTerms,
                'lpo_number' => Order::generateLPONumber()
            ];

            return view('procurement.lpo.create.contract-based', compact('contract', 'suppliers', 'lpoData'));
        } catch (\Exception $e) {
            Log::error('Create LPO from Contract Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create LPO from contract: ' . $e->getMessage());
        }
    }

    /**
     * Create LPO from award
     */
    public function createFromAward(Request $request, $awardId)
    {
        try {
            $award = TenderAward::with(['tender', 'winningSupplier'])
                ->findOrFail($awardId);

            if (!$award->is_approved) {
                return redirect()->back()->with('error', 'Only approved awards can be used for LPO creation.');
            }

            // Get suppliers for this award (primary is the winning supplier)
            $suppliers = collect([$award->winningSupplier]);

            // Pre-fill LPO data from award
            $lpoData = [
                'origination_type' => 'award',
                'award' => $award,
                'supplier' => $award->winningSupplier,
                'tender_no' => $award->tender->TenderNo,
                'award_amount' => $award->AwardAmount,
                'lpo_number' => Order::generateLPONumber()
            ];

            return view('procurement.lpo.create.award-based', compact('award', 'suppliers', 'lpoData'));
        } catch (\Exception $e) {
            Log::error('Create LPO from Award Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create LPO from award: ' . $e->getMessage());
        }
    }

    /**
     * Create LPO from direct procurement plan
     */
    public function createFromDirectProcurement(Request $request, $planId)
    {
        try {
            $plan = ConsolidatedProcurementPlan::with(['planLineItems' => function ($query) {
                $query->whereHas('procurementMode', function ($subQuery) {
                    $subQuery->where('Description', 'LIKE', '%Direct%')
                        ->orWhere('Description', 'LIKE', '%direct%');
                })
                    ->where('ExecutionStatus', 'Pending')
                    ->with(['item', 'procurementMode']);
            }])
                ->findOrFail($planId);

            if ($plan->Status !== 'Approved') {
                return redirect()->back()->with('error', 'Only approved procurement plans can be used for LPO creation.');
            }

            if ($plan->planLineItems->isEmpty()) {
                return redirect()->back()->with('error', 'No direct procurement items found in this plan.');
            }

            // Get all suppliers (user needs to select appropriate supplier)
            $suppliers = Supplier::where('IsActive', true)
                ->orderBy('SupplierName')
                ->get();

            // Pre-fill LPO data from plan
            $lpoData = [
                'origination_type' => 'direct_procurement',
                'plan' => $plan,
                'plan_items' => $plan->planLineItems,
                'estimated_total' => $plan->planLineItems->sum(function ($item) {
                    return $item->MergedQty * $item->EstimatedUnitCost;
                }),
                'lpo_number' => Order::generateLPONumber()
            ];

            return view('procurement.lpo.create.direct-procurement', compact('plan', 'suppliers', 'lpoData'));
        } catch (\Exception $e) {
            Log::error('Create LPO from Direct Procurement Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create LPO from direct procurement: ' . $e->getMessage());
        }
    }

    /**
     * Store contract-based LPO
     */
    public function storeContractBasedLPO(Request $request)
    {
        try {
            $validated = $request->validate([
                'contract_id' => 'required|exists:t_TenderAwards,Id',
                'lpo_number' => 'required|string|max:100',
                'lpo_date' => 'required|date',
                'priority' => 'required|in:High,Medium,Low',
                'expected_delivery_date' => 'required|date|after:today',
                'payment_terms' => 'nullable|string',
                'delivery_terms' => 'nullable|string',
                'notes' => 'nullable|string',
                'total_amount' => 'required|numeric|min:0',
                'items' => 'required|array|min:1',
                'items.*.description' => 'required|string',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.unit' => 'nullable|string',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.tax_percentage' => 'nullable|numeric|min:0|max:100',
                'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
                'items.*.total_amount' => 'required|numeric|min:0'
            ]);

            $contract = TenderAward::findOrFail($validated['contract_id']);
            $action = $request->input('action', 'submit');

            DB::beginTransaction();

            // Create the Order (LPO)
            $order = Order::create([
                'OrderNo' => $validated['lpo_number'],
                'OrderDate' => $validated['lpo_date'],
                'Terms' => $validated['payment_terms'],
                'Priority' => $validated['priority'],
                'AccountID' => $contract->SupplierId,
                'Status' => $action === 'save_draft' ? 'draft' : 'pending_approval',
                'OriginationType' => 'contract',
                'ContractRef' => $contract->Id,
                'OriginationRef' => $contract->ContractRef,
                'TotalAmount' => $validated['total_amount'],
                'Notes' => $validated['notes'],
                'DeliveryTerms' => $validated['delivery_terms'],
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id()
            ]);

            // Create Order Lines using correct column names
            foreach ($validated['items'] as $itemData) {
                OrderLines::create([
                    'iOrderID' => $order->Id,                              // Actual column name
                    'cDescription' => $itemData['description'],            // Actual column name
                    'fQuantity' => $itemData['quantity'],                  // Actual column name
                    'UnitOfMeasure' => $itemData['unit'],                  // New column we're adding
                    'fUnitPriceExcl' => $itemData['unit_price'],          // Actual column name
                    'TaxPercentage' => $itemData['tax_percentage'] ?? 0,   // New column we're adding
                    'DiscountPercentage' => $itemData['discount_percentage'] ?? 0, // New column we're adding
                    'LineTotal' => $itemData['total_amount'],              // Actual column name
                    'cLineNotes' => $itemData['notes'] ?? '',             // Actual column name
                    'CreatedBy' => Auth::id(),
                    'ModifiedBy' => Auth::id()
                ]);
            }

            DB::commit();

            $message = $action === 'save_draft'
                ? 'Contract-based LPO draft saved successfully!'
                : 'Contract-based LPO created successfully!';

            return redirect()->route('purchaseOrder.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Contract-based LPO creation failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->withErrors(['general' => 'Failed to create LPO: ' . $e->getMessage()]);
        }
    }

    /**
     * Store award-based LPO
     */
    public function storeAwardBasedLPO(Request $request)
    {
        // Similar implementation for award-based LPOs
        // TODO: Implement award-based LPO storage
        return redirect()->back()->with('info', 'Award-based LPO creation coming soon!');
    }

    /**
     * Store direct procurement LPO
     */
    public function storeDirectProcurementLPO(Request $request)
    {
        // Similar implementation for direct procurement LPOs
        // TODO: Implement direct procurement LPO storage
        return redirect()->back()->with('info', 'Direct procurement LPO creation coming soon!');
    }

    // **PRIVATE HELPER METHODS**

    private function getContractBasedLPOsCount(): int
    {
        return TenderAward::where('ContractStatus', 'Executed')->count();
    }

    private function getAwardBasedLPOsCount(): int
    {
        return TenderAward::where('is_approved', true)
            ->where(function ($query) {
                $query->whereNull('ContractStatus')
                    ->orWhere('ContractStatus', '!=', 'Executed');
            })
            ->count();
    }

    private function getDirectProcurementLPOsCount(): int
    {
        return ConsolidatedProcurementPlan::where('Status', 'Approved')
            ->whereHas('planLineItems', function ($query) {
                $query->whereHas('procurementMode', function ($subQuery) {
                    $subQuery->where('Description', 'LIKE', '%Direct%')
                        ->orWhere('Description', 'LIKE', '%direct%');
                })
                    ->where('ExecutionStatus', 'Pending');
            })
            ->count();
    }
}
