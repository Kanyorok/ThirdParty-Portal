<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Enums\ProcurementPlanStatusEnum;
use App\Enums\TenderApprovalStatusEnum;
use App\Enums\TenderCategoryEnum;
use App\Enums\TenderStatusEnum;
use App\Enums\TenderTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\Currency;
use App\Models\DMS\Document;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\ItemMasterList;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\ModeTimeline;
use App\Models\Procurement\PlanLineItem;
use App\Models\Procurement\ProcurementMode;
use App\Models\Procurement\ProcurementPlan;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderCategory;
use App\Models\Procurement\TenderItems;
use App\Models\Procurement\TenderStage;
use App\Models\Procurement\TenderSupplier;
use App\Models\ThirdParies\Supplier;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Throwable;
use App\Models\Procurement\TenderInvitation;
use App\Services\Workflow\ApprovalWorkflow;
use App\Mail\TenderInvitation as TenderInvitationMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Auth\User;
use Illuminate\Http\UploadedFile;
use App\Models\Procurement\TenderDocument;
use App\Enums\EmailPriorityEnum;
use App\Enums\Core\ModulesEnum;
use App\Models\DMS\Image;


class TenderController extends Controller
{

    protected ApprovalWorkflow $workflow;
    public function __construct(ApprovalWorkflow $workflow)
    {
        $this->workflow = $workflow;  // Injected with codeId via service container
        $this->middleware('auth');
    }
    public function index()
    {
        $this->authorize(PermissionEnum::TenderRead, Tender::class);
        $tenders = Tender::with(['procurementMode', 'currency'])->get();

        activity()
            ->performedOn(new Tender())
            ->causedBy(Auth::user())
            ->withProperties(['action' => 'viewed'])
            ->log('Viewed tenders list');

        return view('procurement.tendering.tendersetup.tenderinitiation.index', compact('tenders'));
    }

    //b4
    public function create()
    {
        $this->authorize(PermissionEnum::TenderWrite, Tender::class);

        $procurementModes = ProcurementMode::all();
        $currencies = Currency::all();
        $tenderTypes = TenderTypeEnum::cases();
        $statuses = TenderStatusEnum::cases();
        $tenderCategories = TenderCategory::select('Id', 'TenderCategory')->get();
        $allItemsCategories = ItemCategories::select('Id', 'Name')->whereNull('ParentId')->get();
        $allCurrency = Currency::select('Id', 'Name', 'Code', 'Symbol')->get();

        // Build category hierarchy map
        $allCategories = ItemCategories::select('Id', 'ParentId')->get()->keyBy('Id');
        $categoryToTopLevel = [];
        foreach ($allCategories as $category) {
            $current = $category;
            while ($current->ParentId !== null && isset($allCategories[$current->ParentId])) {
                $current = $allCategories[$current->ParentId];
            }
            $categoryToTopLevel[$category->Id] = $current->Id;
        }

        // Get active items with prices
        $allItemsWithCategoryIds = ItemMasterList::select('Id', 'ItemName', 'Category')
            ->whereNull('DeletedOn')
            ->whereHas('price', function ($q) {
                $q->whereNotNull('ActualPrice')->where('ActualPrice', '>', 0);
            })
            ->orderBy('ItemName')
            ->get()
            ->map(function ($item) use ($categoryToTopLevel) {
                return [
                    'Id' => $item->Id,
                    'ItemName' => $item->ItemName,
                    'Category' => $categoryToTopLevel[$item->Category] ?? $item->Category,
                ];
            });



        // Get all approved procurement plans
        $procurementPlans = ConsolidatedProcurementPlan::select('PlanID', 'ReferenceNumber', 'Title')
            ->where('Status', ProcurementPlanStatusEnum::Approved)
            ->get()
            ->keyBy('PlanID');

        $approvedPlanIds = $procurementPlans->keys();

        //  Calculate used quantities MORE ACCURATELY
        // Group by PlanItemID and sum only QtyToTender (not PlannedQty)
        $usedQuantities = DB::table('t_TenderItems')
            ->whereNotNull('PlanItemID')
            ->whereNull('DeletedOn') // Exclude soft-deleted items
            ->select('PlanItemID', DB::raw('SUM(QtyToTender) as UsedQty'))
            ->groupBy('PlanItemID')
            ->pluck('UsedQty', 'PlanItemID')
            ->map(fn($val) => (float)$val);



        // Get plan line items with tender procurement method
        $itemsCategories = PlanLineItem::select('LineItemID', 'PlanID', 'ItemID', 'MergedQty', 'BranchID', 'DepartmentID', 'ProcurementMethod')
            ->with([
                'item' => function ($query) {
                    $query->select('Id', 'ItemName');
                },
                'procurementMode',
                'departmentNeed' => function ($q) {
                    $q->select('NeedID', 'ItemID', 'BranchID', 'DepartmentID');
                }
            ])
            ->whereIn('PlanID', $approvedPlanIds)
            ->whereHas('procurementMode', function ($q) {
                $q->where('Description', 'like', '%Tender%');
            })
            ->get();



        $procurementPlansOutput = [];
        $planItemData = [];
        $plansWithRemainingItems = [];

        foreach ($itemsCategories as $lineItem) {
            $planId = $lineItem->PlanID;
            $item = $lineItem->item;

            if (!$item) {
                Log::warning("Skipping line item {$lineItem->LineItemID}: No item found");
                continue;
            }

            //  More robust quantity calculation
            $plannedQty = (float)($lineItem->MergedQty ?? 0);
            $usedQty = (float)($usedQuantities[$lineItem->LineItemID] ?? 0);
            $remainingQty = $plannedQty - $usedQty;



            //  Use a small threshold (0.01) instead of strict > 0
            if ($remainingQty < 0.01) {

                continue;
            }

            // Mark this plan as having available items
            $plansWithRemainingItems[$planId] = true;

            $needId = optional($lineItem->departmentNeed)->NeedID;

            $entry = [
                'id' => $planId,
                'planLineItemId' => $lineItem->LineItemID,
                'itemId' => $item->Id,
                'name' => $item->ItemName,
                'plannedQty' => $plannedQty,
                'usedQty' => $usedQty,
                'remainingQty' => round($remainingQty, 2), // Round for display
                'needId' => $needId,
            ];

            $procurementPlansOutput[$planId][] = $entry;
            $planItemData[$planId][] = $entry;
        }

        // Filter procurement plans to only show those with remaining items
        $procurementPlan = $procurementPlans->filter(function ($plan) use ($plansWithRemainingItems) {
            return isset($plansWithRemainingItems[$plan->PlanID]);
        })->values();





        // Get suppliers
        $suppliers = $this->getPrequalifiedSuppliers();

        activity()
            ->performedOn(new Tender())
            ->causedBy(Auth::user())
            ->withProperties(['action' => 'create'])
            ->log('View tender creation');

        return view('procurement.tendering.tendersetup.tenderinitiation.create', compact(
            'procurementModes',
            'currencies',
            'tenderTypes',
            'tenderCategories',
            'statuses',
            'itemsCategories',
            'procurementPlan',
            'allItemsCategories',
            'procurementPlansOutput',
            'procurementPlans',
            'planItemData',
            'suppliers',
            'allItemsWithCategoryIds',
            'allCurrency'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tender_category_id' => 'required|integer|exists:t_TenderCategories,Id',
            'item_category_id' => 'required|integer|exists:t_ItemCategories,Id',
            // 1. Submission Deadline must be today or in the future
            'submission_deadline' => 'required|date|after_or_equal:today',

            // 2. Opening Date must be AFTER the submission deadline
            'opening_date' => 'required|date|after:submission_deadline',
            'title' => 'required|string|max:255',
            'tender_type' => 'required|string',
            'scope_of_work' => 'nullable|string',
            'instructions' => 'nullable|string',
            'currency_id' => 'required|integer|exists:t_Currencies,Id',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
        ], [
            'submission_deadline.after_or_equal' => 'The submission deadline cannot be in the past.',
            'opening_date.after' => 'The opening date must be after the submission deadline.',
        ]);

        DB::beginTransaction();

        try {
            // : Create tender in DRAFT status, ApprovalStatus = NULL (not submitted yet)
            $tender = $this->createTenderModel($request);

            $tenderId = $tender->Id;

            // Track procurement plan
            $procurementPlanId = null;
            $planItems = $request->input('plan_items', []);

            // Track procurement plan
            $procurementPlanId = null;
            $planItems = $request->input('plan_items', []);

            if (!empty($planItems)) {
                $firstPlanItem = reset($planItems);
                $procurementPlanId = $firstPlanItem['plan_id'] ?? null;

                if ($procurementPlanId) {
                    $tender->update(['ProcurementPlanId' => $procurementPlanId]);
                }
            }

            // Document upload
            $this->attachDocuments($request, $tender);

            // Get used quantities
            $usedQuantities = TenderItems::whereNotNull('PlanItemID')
                ->selectRaw('PlanItemID, SUM(QtyToTender) as UsedQty')
                ->groupBy('PlanItemID')
                ->pluck('UsedQty', 'PlanItemID');

            // Process plan items
            if (!empty($planItems)) {
                $allowedTypeIds = $this->allowedItemTypeIdsForTender((int)$tender->TenderCategory);

                foreach ($planItems as $compositeKey => $item) {
                    $split = explode('-', $compositeKey);
                    $planItemId = (int)end($split);
                    $itemId = $item['item_id'] ?? null;
                    $qtyRequested = (float)($item['qty'] ?? 0);

                    // Validate remaining quantity
                    $planLineItem = PlanLineItem::find($planItemId);
                    if (!$planLineItem) {
                        Log::warning("Plan line item not found: {$planItemId}");
                        continue;
                    }

                    $plannedQty = (float)$planLineItem->MergedQty;
                    $usedQty = (float)($usedQuantities[$planItemId] ?? 0);
                    $remainingQty = $plannedQty - $usedQty;

                    if ($qtyRequested > $remainingQty) {
                        DB::rollBack();
                        return redirect()->back()
                            ->withErrors(['error' => "Item '{$planLineItem->item->ItemName}' only has {$remainingQty} remaining, but {$qtyRequested} was requested."])
                            ->withInput();
                    }

                    // Validate item type
                    if ($itemId && !$this->isItemAllowedForTender((int)$itemId, (int)$tender->ItemCategoryId, $allowedTypeIds)) {
                        Log::warning("Plan item $itemId rejected for tender due to category/type mismatch");
                        continue;
                    }
                    //plan item reference
                    $prReference = $this->generatePlanItemPRReference($planLineItem->PlanID, $planItemId);

                    // Log::info('Creating PLAN item with auto-generated PR', [
                    //     'plan_id' => $planLineItem->PlanID,
                    //     'plan_item_id' => $planItemId,
                    //     'pr_reference' => $prReference
                    // ]);

                    TenderItems::create([
                        'TenderID' => $tenderId,
                        'SourceType' => 'PLAN',
                        'ItemID' => $itemId,
                        'PlanItemID' => $planItemId,
                        'PlannedQty' => $plannedQty,
                        'QtyToTender' => $qtyRequested,
                        'ItemCategory' => $request->item_category_id,
                        'Remarks' => null,
                        'RelatedPRID' => $prReference,
                        'CreatedBy' => Auth::id(),
                        'ModifiedBy' => Auth::id(),
                    ]);
                }
            }

            // Process manual items
            $manualItems = $request->input('manual_items', []);
            if (!empty($manualItems)) {
                $allowedTypeIds = $this->allowedItemTypeIdsForTender((int)$tender->TenderCategory);
                $manualCounter = 1;
                foreach ($manualItems as $manualItem) {
                    if (empty($manualItem['item_id'])) {
                        continue;
                    }

                    if (!$this->isItemAllowedForTender((int)$manualItem['item_id'], (int)$tender->ItemCategoryId, $allowedTypeIds)) {
                        Log::warning("Manual item {$manualItem['item_id']} rejected for tender due to category/type mismatch");
                        continue;
                    }
                    //auto generated pr reference
                    $prReference = $this->generateManualItemPRReference($tender->TenderNo, $manualCounter++);

                    TenderItems::create([
                        'TenderID' => $tenderId,
                        'SourceType' => 'MANUAL',
                        'ItemID' => $manualItem['item_id'] ?? null,
                        'ManualItemDescription' => null,
                        'PlannedQty' => null,
                        'QtyToTender' => $manualItem['qty'],
                        'ItemCategory' => $request->item_category_id,
                        'Remarks' => null,
                        'RelatedPRID' => $prReference,
                        'CreatedBy' => Auth::id(),
                        'ModifiedBy' => Auth::id(),
                    ]);
                }
            }

            // Process suppliers
            $suppliers = $request->input('suppliers', []);
            if (!empty($suppliers)) {
                foreach ($suppliers as $supplierId) {
                    TenderSupplier::create([
                        'TenderID' => $tenderId,
                        'SupplierID' => $supplierId,
                        'CreatedBy' => Auth::id(),
                        'ModifiedBy' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            activity()
                ->performedOn($tender)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Tender created successfully with ID: ' . $tenderId);

            return redirect()->route('initiatetender.show', $tenderId)
                ->with('success', 'Tender created successfully. Review and submit for approval when ready.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("--- CREATE TENDER ERROR --- " . $e->getMessage());
            return redirect()->route('initiatetender.index')->with('error', 'Failed to create Tender. Please try again.');
        }
    }

    // Submit tender for approval
    public function submitForApproval($id)
    {
        $this->authorize(PermissionEnum::TenderUpdate, Tender::class);

        DB::beginTransaction();
        try {
            $tender = Tender::findOrFail($id);
            $user = Auth::user();



            // Validation - Status must be Draft
            if ($tender->Status !== TenderStatusEnum::Draft) {
                return redirect()->back()->with('error', 'Only draft tenders can be submitted.');
            }

            // : Check that ApprovalStatus is NULL (not yet submitted)
            if ($tender->ApprovalStatus !== null) {
                return redirect()->back()->with('error', 'This tender has already been submitted for approval.');
            }

            // Check if tender has items
            $itemCount = TenderItems::where('TenderID', $tender->Id)->count();
            if ($itemCount === 0) {
                return redirect()->back()->with('error', 'Cannot submit tender without items.');
            }

            // For restricted tenders, ensure suppliers are selected
            if ($tender->TenderType === TenderTypeEnum::Restricted) {
                $supplierCount = TenderSupplier::where('TenderID', $tender->Id)->count();
                if ($supplierCount === 0) {
                    return redirect()->back()->with('error', 'Restricted tenders must have suppliers.');
                }
            }

            // Update approval status to PENDING
            $tender->update([
                'ApprovalStatus' => TenderApprovalStatusEnum::PENDING,
                'ModifiedBy' => $user->Id,
                'ModifiedOn' => now(),
            ]);

            // Submit to workflow
            $submitted = $this->workflow->submit(
                $tender,
                $user,
                TenderApprovalStatusEnum::PENDING,
                'Tender submitted for approval'
            );

            if (!$submitted) {
                throw new \Exception('Failed to submit to workflow');
            }

            DB::commit();

            activity()
                ->performedOn($tender)
                ->causedBy($user)
                ->withProperties([
                    'action' => 'submit_for_approval',
                    'previous_status' => 'Draft'
                ])
                ->log('Tender submitted for approval: ' . $tender->TenderNo);

            return redirect()->route('initiatetender.show', $id)
                ->with('success', 'Tender submitted for approval successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Submit for approval failed', [
                'tender_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Failed to submit tender: ' . $e->getMessage());
        }
    }
    public function show(string $id)
    {
        $this->authorize(PermissionEnum::TenderRead, Tender::class);

        try {
            if (!Auth::check()) {
                return redirect()->route('login')->with('error', 'Please login to view tenders');
            }

            $user = Auth::user();
            $userId = $user->Id ?? null;

            if (!$userId) {
                return redirect()->route('login')->with('error', 'Session error. Please login again.');
            }

            $tender = Tender::with(['procurementPlan'])->findOrFail($id);

            // Check permissions
            $isSubmitter = ($tender->CreatedBy == $userId);
            $canApprove = false;
            $showApprovalButtons = false;
            $canEdit = false;

            //  Determine edit permissions
            // Can edit if: (1) Status is Draft AND (2) Not yet submitted (ApprovalStatus is NULL) OR rejected
            if ($tender->Status === TenderStatusEnum::Draft) {
                if ($tender->ApprovalStatus === null || $tender->ApprovalStatus === TenderApprovalStatusEnum::REJECTED) {
                    $canEdit = $isSubmitter; // Only creator can edit
                }
            }

            // Only check approval permissions if tender is pending
            if ($tender->ApprovalStatus === TenderApprovalStatusEnum::PENDING) {
                $canApprove = $this->workflow->canApproveModel($tender, $user);

                // Submitter cannot approve their own tender
                if ($isSubmitter) {
                    $canApprove = false;
                }

                $showApprovalButtons = $canApprove && !$isSubmitter;
            }



            // Get tender items with relationships
            $items = TenderItems::where('TenderID', $id)
                ->with([
                    'item.price',
                    'category',
                    'planLineItem' => function ($query) {
                        $query->with([
                            'procurementPlan:PlanID,ReferenceNumber,Title',
                            'departmentNeed:NeedID,ItemID,BranchID,DepartmentID'
                        ]);
                    }
                ])
                ->get();

            // Separate plan and manual items
            $planItems = $items->where('SourceType', 'PLAN')->map(function ($tenderItem) {
                $planLineItem = $tenderItem->planLineItem;
                return [
                    'id' => $tenderItem->Id,
                    'item' => $tenderItem->item,
                    'planReference' => $planLineItem?->procurementPlan?->ReferenceNumber ?? 'N/A',
                    'planTitle' => $planLineItem?->procurementPlan?->Title ?? 'N/A',
                    'needId' => $planLineItem?->departmentNeed?->NeedID ?? 'N/A',
                    'plannedQty' => $tenderItem->PlannedQty ?? 0,
                    'qtyToTender' => $tenderItem->QtyToTender ?? 0,
                    'unitPrice' => $tenderItem->item?->price?->ActualPrice ?? 0,
                    'totalPrice' => ($tenderItem->QtyToTender ?? 0) * ($tenderItem->item?->price?->ActualPrice ?? 0),
                    'remarks' => $tenderItem->Remarks,
                ];
            });

            $manualItems = $items->where('SourceType', 'MANUAL')->map(function ($tenderItem) {
                return [
                    'id' => $tenderItem->Id,
                    'item' => $tenderItem->item,
                    'description' => $tenderItem->ManualItemDescription,
                    'qtyToTender' => $tenderItem->QtyToTender ?? 0,
                    'unitPrice' => $tenderItem->item?->price?->ActualPrice ?? 0,
                    'totalPrice' => ($tenderItem->QtyToTender ?? 0) * ($tenderItem->item?->price?->ActualPrice ?? 0),
                    'remarks' => $tenderItem->Remarks,
                ];
            });

            // Calculate total cost
            $totalEstimatedCost = $items->sum(function ($item) {
                return ($item->QtyToTender ?? 0) * ($item->item?->price?->ActualPrice ?? 0);
            });

            // Get related data
            $suppliers = TenderSupplier::where('TenderID', $id)
                ->with('supplier.party')
                ->get();
            $tenderCategory = TenderCategory::find($tender->TenderCategory);
            $itemCategory = ItemCategories::find($tender->ItemCategoryId);
            $currency = Currency::find($tender->CurrencyId);

            try {
                $documents = $tender->documents;
            } catch (\Exception $e) {
                Log::error("Failed to load documents: " . $e->getMessage());
                $documents = collect();
            }

            $procurementPlan = $tender->procurementPlan;

            return view('procurement.tendering.tendersetup.tenderinitiation.show', compact(
                'tender',
                'tenderCategory',
                'itemCategory',
                'currency',
                'planItems',
                'manualItems',
                'items',
                'suppliers',
                'canApprove',
                'showApprovalButtons',
                'isSubmitter',
                'canEdit',
                'totalEstimatedCost',
                'documents',
                'procurementPlan'
            ));
        } catch (\Exception $e) {
            Log::error('Error in show method', [
                'tender_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('initiatetender.index')
                ->with('error', 'Failed to load tender: ' . $e->getMessage());
        }
    }

    ////before



    public function edit(string $id)
    {
        $this->authorize(PermissionEnum::TenderUpdate, Tender::class);
        $tender = Tender::findOrFail($id);

        $show = true;
        if (
            $tender->ApprovalStatus === TenderApprovalStatusEnum::REJECTED ||
            $tender->ApprovalStatus === TenderApprovalStatusEnum::APPROVED
        ) {
            $show = true;
        }

        $items = TenderItems::where('TenderID', $id)
            ->where('ItemCategory', $tender->ItemCategoryId)
            ->get();

        // Build category hierarchy map
        $allCategories = ItemCategories::select('Id', 'ParentId')->get()->keyBy('Id');
        $categoryToTopLevel = [];
        foreach ($allCategories as $category) {
            $current = $category;
            while ($current->ParentId !== null && isset($allCategories[$current->ParentId])) {
                $current = $allCategories[$current->ParentId];
            }
            $categoryToTopLevel[$category->Id] = $current->Id;
        }

        // Get allowed item types
        $allowedTypeIds = $this->allowedItemTypeIdsForTender((int)$tender->TenderCategory);

        // If no type restrictions configured, allow all items in category**
        $checkItemTypes = !empty($allowedTypeIds);

        // Get items matching category (and optionally type)
        // If Tender is linked to a Procurement Plan, ONLY show items from that Plan
        $planId = $tender->ProcurementModeId; // Assuming ProcurementModeId acts as the Plan ID link

        if ($planId) {
            // Fetch items from the plan
            $planItems = PlanLineItem::where('PlanID', $planId)
                ->whereNull('DeletedOn')
                ->with(['item'])
                ->get();

            // Get IDs of items already in this tender to exclude them
            $existingItemIds = $items->pluck('ItemId')->toArray();

            $otherItemsForThatTender = $planItems->filter(function ($planItem) use ($categoryToTopLevel, $tender, $allowedTypeIds, $checkItemTypes, $existingItemIds) {
                $item = $planItem->item;
                if (!$item) return false;

                // Exclude already added items
                if (in_array($item->Id, $existingItemIds)) return false;

                // Check if item's top-level category matches tender's category
                $itemTopCategory = $categoryToTopLevel[$item->Category] ?? $item->Category;
                if ($itemTopCategory != $tender->ItemCategoryId) {
                    return false;
                }

                // If type checking is disabled (no mappings), allow all items in category
                if (!$checkItemTypes) {
                    return true;
                }

                // Otherwise, check item type
                return $this->isItemAllowedForTender(
                    (int)$item->Id,
                    (int)$tender->ItemCategoryId,
                    $allowedTypeIds
                );
            })
                ->map(function ($planItem) {
                    $item = $planItem->item;
                    return [
                        'Id' => $item->Id,
                        'ItemName' => $item->ItemName . ' (Plan Ref: ' . $planItem->Id . ')', // Add visual cue
                    ];
                })
                ->sortBy('ItemName')
                ->values();
        } else {
            // Fallback: Fetch ALL items matching category (for tenders without plans)
            $otherItemsForThatTender = ItemMasterList::select('Id', 'ItemName', 'Category', 'ItemType')
                ->whereNull('DeletedOn')
                ->whereHas('price', function ($q) {
                    $q->whereNotNull('ActualPrice')->where('ActualPrice', '>', 0);
                })
                ->get()
                ->filter(function ($item) use ($categoryToTopLevel, $tender, $allowedTypeIds, $checkItemTypes) {
                    // Check if item's top-level category matches tender's category
                    $itemTopCategory = $categoryToTopLevel[$item->Category] ?? $item->Category;
                    if ($itemTopCategory != $tender->ItemCategoryId) {
                        return false;
                    }

                    // If type checking is disabled (no mappings), allow all items in category
                    if (!$checkItemTypes) {
                        return true;
                    }

                    // Otherwise, check item type
                    return $this->isItemAllowedForTender(
                        (int)$item->Id,
                        (int)$tender->ItemCategoryId,
                        $allowedTypeIds
                    );
                })
                ->map(function ($item) {
                    return [
                        'Id' => $item->Id,
                        'ItemName' => $item->ItemName,
                    ];
                })
                ->sortBy('ItemName')
                ->values();
        }

        // Rest of your code remains the same...
        $suppliers = TenderSupplier::where('TenderID', $id)
            ->with(['supplier.party'])
            ->get();


        // Load documents - Get documents related to this tender
        try {
            $documents = $tender->documents()->with('repository')->get();
        } catch (\Exception $e) {
            Log::error("Failed to load documents in edit: " . $e->getMessage());
            $documents = collect();
        }

        $existingSupplierIds = collect($suppliers)->pluck('SupplierID')->map(fn($v) => (int)$v)->values();
        $topCategoryId = (int) $tender->ItemCategoryId;
        $prequalified = $this->getPrequalifiedSuppliers();

        $otherSuppliers = collect($prequalified)
            ->filter(function ($s) use ($topCategoryId, $existingSupplierIds) {
                $id = (int) ($s['Id'] ?? 0);
                $cats = collect($s['ItemCategoryIds'] ?? []);
                return $id > 0
                    && !$existingSupplierIds->contains($id)
                    && ($topCategoryId ? $cats->contains($topCategoryId) : true);
            })
            ->map(function ($s) {
                return (object) [
                    'Id' => (int) ($s['Id'] ?? 0),
                    'SupplierName' => (string) ($s['ThirdPartyName'] ?? $s['SupplierName'] ?? ''),
                    'ContactEmail' => (string) ($s['Email'] ?? ''),
                    'ContactPhone' => (string) ($s['Phone'] ?? ''),
                ];
            })
            ->values();

        $tenderCategory = TenderCategory::find($tender->TenderCategory)->Id;
        $tenderCategories = TenderCategory::select('Id', 'TenderCategory')->get();
        $itemCategory = ItemCategories::find($tender->ItemCategoryId)->Name;
        $itemCategoryID = ItemCategories::find($tender->ItemCategoryId)->Id;
        $currency = $tender->CurrencyId;
        $allCurrency = Currency::select('Id', 'Name', 'Code', 'Symbol')->get();
        $procurementPlan = ProcurementPlan::find($tender->procurement_plan_id);

        return view('procurement.tendering.tendersetup.tenderinitiation.edit', compact(
            'tender',
            'tenderCategory',
            'tenderCategories',
            'itemCategory',
            'itemCategoryID',
            'currency',
            'procurementPlan',
            'items',
            'otherItemsForThatTender',
            'suppliers',
            'otherSuppliers',
            'show',
            'documents',
            'allCurrency'
        ));
    }

    public function update(Request $request, $id)
    {
        $type = $request->input('type');

        if ($type === 'editTenderInfo') {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'tender_category_id' => 'required|exists:t_TenderCategories,Id',
                'currency_id' => 'required|exists:t_Currencies,Id',
                'submission_deadline' => 'required|date|after:today',
                'opening_date' => 'required|date|after_or_equal:submission_deadline',
                'TenderType' => ['nullable', new Enum(TenderTypeEnum::class)],
                'documents.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
            ]);

            DB::beginTransaction();

            try {
                $tender = Tender::findOrFail($id);
                $tender->Title = $validated['title'];
                $tender->TenderCategory = $validated['tender_category_id'];
                $tender->CurrencyId = $validated['currency_id'];
                $tender->SubmissionDeadline = $validated['submission_deadline'];
                $tender->OpeningDate = $validated['opening_date'];
                if (!empty($validated['TenderType'])) {
                    $tender->TenderType = TenderTypeEnum::from($validated['TenderType']);
                }

                $tender->save();

                // Attach Tender Documents to DMS (from edit form)
                if ($request->hasFile('documents')) {
                    $this->attachDocuments($request, $tender);
                }

                DB::commit();

                activity()
                    ->performedOn($tender)
                    ->causedBy(Auth::user())
                    ->withProperties(['action' => 'update'])
                    ->log('Tender updated successfully with ID: ' . $id);

                return redirect()->route('initiatetender.edit', $id)->with('success', 'Tender Info updated successfully.');
            } catch (Exception $e) {
                DB::rollBack();
                Log::error('--- UPDATE TENDER ERROR --- ' . $e->getMessage());
                return redirect()->route('initiatetender.edit', $id)->with('error', 'Failed to update Tender. Please try again.');
            }
        } elseif ($type == 'crudItem') {
            $crudType = $request->crudType;
            if ($crudType == 'addItem') {
                $validated = $request->validate([
                    'item_id' => 'required|integer',
                    'QtyToTender' => 'required|integer|min:1',

                ]);


                DB::beginTransaction();
                try {
                    $tenderItem = new TenderItems();
                    $tenderItem->TenderID = $id;

                    $existingManualCount = TenderItems::where('TenderID', $id)
                        ->where('SourceType', 'MANUAL')
                        ->count();

                    $prReference = $this->generateManualItemPRReference(
                        $tenderItem->Tender->TenderNo,
                        $existingManualCount + 1
                    );
                    $tenderItem->SourceType = 'MANUAL';
                    $tenderItem->ItemID = $validated['item_id'];
                    $tenderItem->itemCategory = $request->itemCategoryID;
                    $tenderItem->QtyToTender = $validated['QtyToTender'];
                    $tenderItem->RelatedPRID = $prReference;
                    $tenderItem->CreatedBy = Auth::id();
                    $tenderItem->ModifiedBy = Auth::id();
                    $tenderItem->save();



                    DB::commit();

                    activity()
                        ->performedOn($tenderItem)
                        ->causedBy(Auth::user())
                        ->withProperties(['action' => 'create'])
                        ->log('Tender Item created successfully with ID: ' . $tenderItem->Id);

                    return redirect()->route('initiatetender.edit', $id)->with('success', 'Tender Item created successfully.');
                } catch (Exception $e) {
                    DB::rollBack();
                    Log::error("--- CREATE TENDER ITEM ERROR --- " . $e->getMessage());
                    Log::error($e);
                    return redirect()->route('initiatetender.edit', $id)->with('error', 'Failed to create Tender Item. Please try again.');
                }
            } elseif ($crudType == 'deleteItem') {
                DB::beginTransaction();
                try {
                    $tenderItem = TenderItems::findOrFail($request->item_id);
                    $tenderItem->delete();

                    DB::commit();

                    activity()
                        ->performedOn($tenderItem)
                        ->causedBy(Auth::user())
                        ->withProperties(['action' => 'delete'])
                        ->log('Tender Item deleted successfully with ID: ' . $tenderItem->Id);

                    return redirect()->route('initiatetender.edit', $id)->with('success', 'Tender Item deleted successfully.');
                } catch (Exception $e) {
                    DB::rollBack();
                    Log::error("--- DELETE TENDER ITEM ERROR --- " . $e->getMessage());
                    Log::error($e);
                    return redirect()->route('initiatetender.edit', $id)->with('error', 'Failed to delete Tender Item. Please try again.');
                }
            } // Add this to your update() method in the 'updateQty' section:
            elseif ($crudType == 'updateQty') {
                // 1. Validation
                $validated = $request->validate([
                    'item_id' => 'required|integer|exists:t_TenderItems,Id',
                    'QtyToTender' => 'required|numeric|min:0.01',
                ]);

                DB::beginTransaction();
                try {
                    $tenderItem = TenderItems::findOrFail($validated['item_id']);

                    // 2. Security Check: Ensure this item actually belongs to the tender being edited
                    if ($tenderItem->TenderID != $id) {
                        return redirect()->back()->with('error', 'Security Warning: This item does not belong to the current tender.');
                    }

                    // 3. Logic Check: Ensure it is a manual item
                    if (strtoupper((string)$tenderItem->SourceType) !== 'MANUAL') {
                        return redirect()->route('initiatetender.edit', $id)
                            ->with('error', 'Only manually added items can be adjusted.');
                    }

                    // 4. Update Values (Use Direct Assignment Only)
                    // We DO NOT use ->update() here to avoid issues with $fillable arrays
                    $tenderItem->QtyToTender = $validated['QtyToTender'];
                    $tenderItem->ModifiedBy = Auth::id();
                    $tenderItem->ModifiedOn = now();

                    // 5. Save Once
                    $tenderItem->save();

                    DB::commit();

                    activity()
                        ->performedOn($tenderItem)
                        ->causedBy(Auth::user())
                        ->withProperties(['action' => 'updateQty', 'new_qty' => $validated['QtyToTender']])
                        ->log('Tender Item quantity updated for ID: ' . $tenderItem->Id);

                    return redirect()->route('initiatetender.edit', $id)
                        ->with('success', 'Quantity updated successfully.');
                } catch (Exception $e) {
                    DB::rollBack();
                    Log::error("--- UPDATE TENDER ITEM QTY ERROR --- " . $e->getMessage());
                    return redirect()->route('initiatetender.edit', $id)
                        ->with('error', 'Failed to update quantity.');
                }
            }
        } elseif ($type == 'crudSupplier') {
            $crudType = $request->crudType;
            if ($crudType == 'addSupplier') {
                $validated = $request->validate([
                    'supplier_id' => 'required|integer|exists:t_Suppliers,Id',
                ]);
                DB::beginTransaction();
                try {
                    $tenderSupplier = new TenderSupplier();
                    $tenderSupplier->TenderID = $id;
                    $tenderSupplier->SupplierID = $validated['supplier_id'];
                    $tenderSupplier->CreatedBy = Auth::id();
                    $tenderSupplier->ModifiedBy = Auth::id();
                    $tenderSupplier->save();

                    DB::commit();

                    activity()
                        ->performedOn($tenderSupplier)
                        ->causedBy(Auth::user())
                        ->withProperties(['action' => 'create'])
                        ->log('Tender Supplier created successfully with ID: ' . $tenderSupplier->Id);

                    return redirect()->route('initiatetender.edit', $id)->with('success', 'Tender Supplier created successfully.');
                } catch (Exception $e) {
                    DB::rollBack();
                    Log::error("--- CREATE TENDER SUPPLIER ERROR --- " . $e->getMessage());
                    Log::error($e);
                    return redirect()->route('initiatetender.edit', $id)->with('error', 'Failed to create Tender Supplier. Please try again.');
                }
            } elseif ($crudType == 'deleteSupplier') {
                DB::beginTransaction();
                try {
                    $tenderSupplier = TenderSupplier::findOrFail($request->supplier_id);
                    $tenderSupplier->delete();

                    DB::commit();

                    activity()
                        ->performedOn($tenderSupplier)
                        ->causedBy(Auth::user())
                        ->withProperties(['action' => 'delete'])
                        ->log('Tender Supplier deleted successfully with ID: ' . $tenderSupplier->Id);

                    return redirect()->route('initiatetender.edit', $id)->with('success', 'Tender Supplier deleted successfully.');
                } catch (Exception $e) {
                    DB::rollBack();
                    Log::error("--- DELETE TENDER SUPPLIER ERROR --- " . $e->getMessage());
                    Log::error($e);
                    return redirect()->route('initiatetender.edit', $id)->with('error', 'Failed to delete Tender Supplier. Please try again.');
                }
            }
        }

        return redirect()->route('initiatetender.index')->with('error', 'Invalid request type.');

        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'TenderType' => ['required', new Enum(TenderTypeEnum::class)],
            'TenderCategory' => ['required', new Enum(TenderCategoryEnum::class)],
            'ScopeOfWork' => 'nullable|string',
            'Instructions' => 'nullable|string',
            'SubmissionDeadline' => 'required|date|after_or_equal:StartDate',
            'OpeningDate' => 'required|date|after:SubmissionDeadline',
            'Status' => ['required', new Enum(TenderStatusEnum::class)],
            'RelatedPRID' => 'nullable|integer',
            'ProcurementModeId' => 'required|integer|exists:t_ProcurementModes,id',
            'Currency' => 'required|exists:t_Currencies,Id',
            'EstimatedValue' => 'nullable|numeric|min:0',
            'StartDate' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $tender = Tender::findOrFail($id);
            $tender->fill($request->only([
                'Title',
                'ScopeOfWork',
                'Instructions',
                'SubmissionDeadline',
                'OpeningDate',
                'RelatedPRID',
                'ProcurementModeId',
                'EstimatedValue',
                'StartDate'
            ]));

            $fillData['CurrencyId'] = $request->Currency;
            $tender->fill($fillData);

            $tender->TenderType = TenderTypeEnum::from($request->TenderType);
            // Store TenderCategory as FK Id instead of enum code
            $catId = $request->input('tender_category_id') ?? $request->input('TenderCategory');
            if (!is_null($catId) && !is_numeric($catId)) {
                $label = match ((string)$catId) {
                    'G', 'Goods', 'goods' => 'Goods',
                    'S', 'Services', 'services' => 'Services',
                    'W', 'Works', 'works' => 'Works',
                    default => null,
                };
                if ($label) {
                    $catId = \App\Models\Procurement\TenderCategory::where('TenderCategory', $label)->value('Id');
                }
            }
            if ($catId) {
                $tender->TenderCategory = (int)$catId;
            }
            $tender->Status = TenderStatusEnum::from($request->Status);
            $tender->ModifiedBy = Auth::id();
            $tender->save();

            if ($request->TenderType === TenderTypeEnum::Restricted->value) {
                $tender->suppliers()->sync($request->suppliers ?? []);
            } else {
                $tender->suppliers()->detach();
            }
            DB::commit();
            activity()
                ->performedOn($tender)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Tender updated successfully with ID: ' . $id);
            return redirect()->route('initiatetender.index')->with('success', 'Tender updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("--- UPDATE TENDER ERROR --- " . $e->getMessage());
            Log::error($e);
            return redirect()->route('initiatetender.index')->with('error', 'Failed to update Tender. Please try again.');
        }
    }

    /**
     * Generate PR reference for PLAN items
     * Format: PR/PLAN-{PlanID}/ITEM-{PlanItemID}
     * Example: PL123-456
     */
    private function generatePlanItemPRReference(int $planId, int $planItemId): string
    {
        return sprintf('PL%d-%d', $planId, $planItemId);
    }

    /**
     * Generate PR reference for MANUAL items
     * Format: PR/{TenderNo}/MAN-{Counter}
     * Example: T6MMNKZA-M001
     */
    private function generateManualItemPRReference(string $tenderNo, int $counter): string
    {
        $shortTenderNo = str_replace('TNDR-', '', $tenderNo);
        return sprintf('%s-M%03d', $shortTenderNo, $counter);
    }

    public function destroy(string $id)
    {
        $this->authorize(PermissionEnum::TenderDelete, Tender::class);
        try {
            $tender = Tender::findOrFail($id);
            $tender->delete();
            activity()
                ->performedOn($tender)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Tender deleted successfully with ID: ' . $id);
            return redirect()->route('initiatetender.index')->with('success', 'Tender deleted successfully.');
        } catch (Throwable $th) {
            Log::error("--- DELETE TENDER ERROR --- " . $th->getMessage());
            Log::error($th);
            return redirect()->route('initiatetender.index')->with('error', 'Failed to delete Tender. Please try again.');
        }
    }

    protected function generateTenderStages(Tender $tender, $procurementModeId, $startDate)
    {
        $timelineStages = ModeTimeline::where('ProcurementModeId', $procurementModeId)->get();
        $startDate = Carbon::parse($startDate);

        foreach ($timelineStages as $stage) {
            $endDate = (clone $startDate)->addDays($stage->DurationDays - 1);

            TenderStage::create([
                'TenderId' => $tender->Id,
                'Stage' => $stage->Stage,
                'DurationDays' => $stage->DurationDays,
                'StartDate' => $startDate,
                'EndDate' => $endDate,
            ]);

            $startDate = $endDate->copy()->addDay();
        }
    }

    public function approveTender(Request $request)
    {
        $this->authorize(PermissionEnum::TenderUpdate, Tender::class);

        $request->validate([
            'tender_id' => 'required|exists:t_Tenders,Id',
            'reason' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $tender = Tender::findOrFail($request->tender_id);
            $user = Auth::user();

            // Log::info("Approval attempt", [
            //     'tender_id' => $tender->Id,
            //     'user_id' => $user->Id,
            //     'current_status' => $tender->ApprovalStatus?->value
            // ]);

            // Check permission
            if (!$this->workflow->canApproveModel($tender, $user)) {
                throw new Exception('You are not authorized to approve this tender');
            }

            // Validate status
            if ($tender->ApprovalStatus !== TenderApprovalStatusEnum::PENDING) {
                throw new Exception('Only pending tenders can be approved');
            }

            // Approve via workflow
            $result = $this->workflow->approve(
                $tender,
                $user,
                TenderApprovalStatusEnum::APPROVED,
                $request->reason,
                'ApprovalStatus'
            );

            if (!$result) {
                Log::error("Workflow approval returned false", [
                    'result' => $result,
                    'type' => gettype($result)
                ]);
                throw new Exception('Workflow approval failed - workflow service returned false');
            }

            // Refresh and update
            $tender->refresh();
            $tender->update([
                'Status' => TenderStatusEnum::Published,
                'ModifiedBy' => $user->Id,
                'ModifiedOn' => now(),
            ]);

            // Handle restricted invitations
            $invitationsSent = 0;
            if ($tender->isRestricted()) {
                $invitationsSent = $this->sendRestrictedTenderInvitations($tender);
            }

            DB::commit();

            activity()
                ->performedOn($tender)
                ->causedBy($user)
                ->withProperties([
                    'action' => 'approve',
                    'remarks' => $request->reason,
                    'invitations_sent' => $invitationsSent
                ])
                ->log('Tender approved: ' . $tender->TenderNo);

            $message = 'Tender approved successfully';
            if ($invitationsSent > 0) {
                $message .= ". Invitations sent to {$invitationsSent} suppliers.";
            }

            return redirect()->route('initiatetender.index')->with('success', $message);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Approval failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to approve: ' . $e->getMessage());
        }
    }

    // AJAX: return distinct top-level item categories for the selected tender category
    public function allowedCategories(Request $request)
    {
        try {
            $tenderCategoryId = $request->input('tender_category_id');

            if (!$tenderCategoryId || !is_numeric($tenderCategoryId)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Valid Tender category ID is required',
                    'categories' => []
                ], 400);
            }

            // Log::info("Fetching allowed categories for tender category: {$tenderCategoryId}");

            // STEP 1: Get allowed ItemType IDs from mapping
            $allowedItemTypeIds = DB::table('t_TenderCategoryItemTypes')
                ->where('TenderCategoryId', $tenderCategoryId)
                ->where('IsActive', 1)
                ->pluck('ItemTypeId')
                ->map(fn($id) => (int)$id)
                ->toArray();

            // Log::info("Found ItemType record IDs", ['item_type_record_ids' => $allowedItemTypeIds]);

            if (empty($allowedItemTypeIds)) {
                return response()->json([
                    'ok' => true,
                    'message' => 'No item types configured for this tender category',
                    'categories' => []
                ]);
            }

            // STEP 2: Get the CodeDetail IDs for these ItemTypes
            $codeDetailIds = DB::table('t_ItemTypes')
                ->whereIn('Id', $allowedItemTypeIds)
                ->where('Active', 1)
                ->pluck('TypeName')
                ->map(fn($id) => (int)$id)
                ->toArray();

            // Log::info("Found CodeDetail IDs for item types", ['code_detail_ids' => $codeDetailIds]);

            if (empty($codeDetailIds)) {
                return response()->json([
                    'ok' => true,
                    'message' => 'No valid item types found',
                    'categories' => []
                ]);
            }

            // STEP 3: Find categories that match EITHER:
            // A) Direct linkage via ItemTypeId (New efficient method)
            // B) Contain items with these types (Legacy fallback)

            // A) Direct linkage
            $directCategoryIds = DB::table('t_ItemCategories')
                ->whereIn('ItemTypeId', $allowedItemTypeIds)
                ->whereNull('DeletedBy')
                ->pluck('Id')
                ->map(fn($id) => (int)$id)
                ->toArray();

            // B) Legacy Item-based linkage
            $itemBasedCategoryIds = DB::table('t_Items as i')
                ->join('t_ItemCategories as ic', 'i.Category', '=', 'ic.Id')
                ->whereIn('i.ItemType', $allowedItemTypeIds)
                ->whereNull('i.DeletedOn')
                ->whereNull('ic.DeletedBy')
                ->distinct()
                ->pluck('ic.Id')
                ->map(fn($id) => (int)$id)
                ->toArray();

            // Merge and unique
            $categoriesWithItems = array_unique(array_merge($directCategoryIds, $itemBasedCategoryIds));

            // Log::info("Found categories", [
            //     'direct_count' => count($directCategoryIds),
            //     'item_based_count' => count($itemBasedCategoryIds),
            //     'total_merged' => count($categoriesWithItems),
            //     'category_ids' => $categoriesWithItems
            // ]);

            // STEP 4: Get top-level parents for all these categories
            $topLevelCategoryIds = [];
            foreach ($categoriesWithItems as $categoryId) {
                $topLevelId = $this->resolveTopLevelCategoryId($categoryId);
                if ($topLevelId) {
                    $topLevelCategoryIds[] = $topLevelId;
                }
            }

            $topLevelCategoryIds = array_unique($topLevelCategoryIds);

            // Log::info("Resolved top-level category IDs", ['top_level_ids' => $topLevelCategoryIds]);

            // STEP 5: Get the actual category records
            $topLevelCategories = DB::table('t_ItemCategories')
                ->whereIn('Id', $topLevelCategoryIds)
                ->whereNull('ParentId')
                ->whereNull('DeletedBy')
                ->select('Id', 'Name')
                ->orderBy('Name')
                ->get();

            // Log::info("Retrieved top-level categories", [
            //     'count' => $topLevelCategories->count(),
            //     'categories' => $topLevelCategories->toArray()
            // ]);

            return response()->json([
                'ok' => true,
                'categories' => $topLevelCategories,
                'message' => 'Categories retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error("Error fetching allowed categories", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'An error occurred while fetching categories',
                'categories' => []
            ], 500);
        }
    }

    //reject a tender
    public function rejectTender(Request $request)
    {
        $this->authorize(PermissionEnum::TenderUpdate, Tender::class);

        $request->validate([
            'tender_id' => 'required|exists:t_Tenders,Id',
            'reason' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $tender = Tender::findOrFail($request->tender_id);
            $user = Auth::user();

            // Check permission
            if (!$this->workflow->canApproveModel($tender, $user)) {
                throw new Exception('You are not authorized to reject this tender');
            }

            if ($tender->ApprovalStatus !== TenderApprovalStatusEnum::PENDING) {
                throw new Exception('Only pending tenders can be rejected');
            }

            // Reject via workflow
            $rejected = $this->workflow->reject(
                $tender,
                $user,
                TenderApprovalStatusEnum::REJECTED,
                $request->reason,
                'ApprovalStatus'
            );

            if (!$rejected) {
                throw new Exception('Workflow rejection failed');
            }

            // Refresh and update
            $tender->refresh();
            $tender->update([
                'Status' => TenderStatusEnum::Draft,
                'ModifiedBy' => $user->Id,
                'ModifiedOn' => now(),
            ]);

            DB::commit();

            activity()
                ->performedOn($tender)
                ->causedBy($user)
                ->withProperties([
                    'action' => 'reject',
                    'remarks' => $request->reason
                ])
                ->log('Tender rejected: ' . $tender->TenderNo);

            return redirect()->route('initiatetender.index')
                ->with('success', 'Tender rejected successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Rejection failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to reject: ' . $e->getMessage());
        }
    }


    /**
     * Get prequalified suppliers based on active prequalification rounds
     */
    private function getPrequalifiedSuppliers()
    {

        // Base supplier query: active suppliers, proper supplier type, with needed relations
        // FIX: 'types' is on ThirdParties (party), not SupplierMaster (thirdParty)
        $supplierQuery = \App\Models\ThirdParies\Supplier::query()
            ->where('Active_Status', 1)
            /* ->whereHas('thirdParty.party.types', function ($q) {
                 $q->where('Code', 'like', 'SU-%');
             })*/
            ->with(['thirdParty.party', 'supplierCategory.itemCategories']);

        $prequalifiedSuppliers = $supplierQuery->get();

        // Log::info('Suppliers fetch — suppliers=' . $prequalifiedSuppliers->count());

        $suppliers = collect();

        foreach ($prequalifiedSuppliers as $supplier) {
            // Check if relationships exist
            if (!$supplier->thirdParty || !$supplier->thirdParty->party) continue;

            $supplierMaster = $supplier->thirdParty;
            $thirdParty = $supplier->thirdParty->party;

            // Build list of item category IDs this supplier can serve
            $itemCategoryIds = [];

            // Start item categories with supplier's direct CategoryId (if present)
            if (!empty($supplier->CategoryId)) {
                $itemCategoryIds[] = (int) $supplier->CategoryId;
                // include first-level subcategories
                $directSubcats = \App\Models\Inventory\ItemCategories::where('ParentId', $supplier->CategoryId)->pluck('Id');
                foreach ($directSubcats as $sid) {
                    $itemCategoryIds[] = (int) $sid;
                }
            }

            // Build supplierCategoryIds from either direct SupplierCategoryID or pivot mapping
            $supplierCategoryIds = collect();
            if (!empty($supplier->SupplierCategoryID)) {
                $supplierCategoryIds->push($supplier->SupplierCategoryID);
            }
            // Add any categories from pivot tables (resilient across DB schemas)
            try {
                // CRITICAL FIX: Pass both ThirdPartyId and SupplierMaster.Id
                // - t_ThirdParty_SupplierCategory uses ThirdPartyId (t_ThirdParties.Id)
                // - t_PrequalificationRoundSupplierCategory uses SupplierMaster.Id
                $pivotCats = \App\Support\SupplierCategoryResolver::getCategoryIdsForThirdParty(
                    (int)$supplierMaster->ThirdPartyId,
                    (int)$supplierMaster->Id  // Pass SupplierMaster.Id for prequalification lookup
                );
                $supplierCategoryIds = $supplierCategoryIds->concat($pivotCats);
            } catch (\Throwable $e) {
                Log::warning('Failed resolving supplier categories', ['supplierId' => $supplier->Id, 'error' => $e->getMessage()]);
            }

            $supplierCategoryIds = $supplierCategoryIds->filter()->unique()->values();

            // From all supplier categories, collect mapped item categories (including all descendants)
            if ($supplierCategoryIds->isNotEmpty()) {
                try {
                    $parentItemCats = DB::table('t_SupplierCategory_ItemCategory')
                        ->whereIn('SupplierCategoryID', $supplierCategoryIds)
                        ->whereNull('DeletedOn')
                        ->pluck('ItemCategoryID');

                    // Expand to all descendants so parent prequalification covers all subcategories
                    $expanded = $this->getAllDescendantCategoryIds($parentItemCats->all(), includeSelf: true);
                    foreach ($expanded as $cid) {
                        $itemCategoryIds[] = (int)$cid;
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed reading category mappings', ['supplierId' => $supplier->Id, 'error' => $e->getMessage()]);
                }
            }

            // Also include top-level ancestors for all collected categories so UI top-level filter matches
            if (!empty($itemCategoryIds)) {
                $topLevelSet = [];
                foreach ($itemCategoryIds as $cid) {
                    $top = $this->resolveTopLevelCategoryId((int)$cid);
                    if ($top) {
                        $topLevelSet[] = (int)$top;
                    }
                }
                $itemCategoryIds = array_merge($itemCategoryIds, $topLevelSet);
            }

            // Log the final ItemCategoryIds for this supplier
            if ($supplierMaster->Id == 2) { // Uma Yang - use SupplierMaster.Id
                // Log::info("Building ItemCategoryIds for supplier (SupplierMaster ID " . $supplierMaster->Id . ")", [
                //     'supplier_name' => $thirdParty->ThirdPartyName,
                //     'supplier_category_ids' => $supplierCategoryIds->toArray(),
                //     'final_item_category_ids' => array_values(array_unique(array_map('intval', $itemCategoryIds))),
                //     'count' => count(array_unique($itemCategoryIds))
                // ]);
            }

            $suppliers->push([
                'Id' => $supplierMaster->Id,  // CRITICAL FIX: Use SupplierMaster.Id, not t_Suppliers.Id
                'SupplierId' => $supplierMaster->Id,  // Explicitly add SupplierId for frontend
                'SupplierName' => $thirdParty->ThirdPartyName,
                'ThirdPartyName' => $thirdParty->ThirdPartyName,
                'Email' => $thirdParty->Email ?? '', // Include Email for restricted tender invitations
                'Phone' => $thirdParty->Phone ?? '',
                'CategoryId' => null, // No longer used - categories come from SupplierCategory mapping
                'SupplierCategoryID' => $supplierCategoryIds->first(), // Prefer first mapped category if any
                'ItemCategoryIds' => array_values(array_unique(array_map('intval', $itemCategoryIds))), // All categories supplier can serve (incl. top-level)
                'RoundID' => $supplier->RoundID,
                'ApplicationStatus' => 'Prequalified', // Since they're in t_Suppliers, they're prequalified
                'ThirdPartyID' => $supplierMaster->ThirdPartyId, // FIX: Use SupplierMaster->ThirdPartyId
            ]);
        }

        // Remove duplicates based on supplier ID (a supplier might have multiple records)
        $result = $suppliers->unique('Id')->values();
        // Log::info('Suppliers prepared for UI: ' . $result->count());
        try {
            if ($result->isNotEmpty()) {
                $sample = $result->take(3);
                // Log::info('Suppliers sample (first 3)', ['sample' => $sample]);
            }
        } catch (\Throwable $e) {
            // guard
        }
        return $result;
    }

    /**
     * Public API for fetching prequalified suppliers for a specific category.
     * Returns JSON format expected by the frontend.
     */
    public function getPrequalifiedSuppliersForCategory($categoryId)
    {
        $suppliers = $this->getPrequalifiedSuppliers();

        // Filter by Category ID
        if ($categoryId) {
            $catIdInt = (int)$categoryId;

            // Log::info("Filtering suppliers for category ID: {$catIdInt}");

            // Get all ancestors of the selected category (including itself)
            // If a supplier is prequalified for any of these ancestors, they are eligible.
            $validCategoryIds = $this->getAllAncestorCategoryIds($catIdInt, includeSelf: true);

            // Log::info("Ancestors for Category {$catIdInt}: " . implode(',', $validCategoryIds));

            $suppliers = $suppliers->filter(function ($s) use ($validCategoryIds) {
                $supplierCategoryIds = $s['ItemCategoryIds'] ?? [];

                // Check if supplier has ANY of the valid ancestor categories
                // array_intersect requires consistent types, ensuring integers
                $supplierCategoryIdsInt = array_map('intval', $supplierCategoryIds);

                $common = array_intersect($supplierCategoryIdsInt, $validCategoryIds);

                if (!empty($common)) {
                    return true;
                }

                return false;
            })->values();

            // Log::info("Filtered suppliers count: {$suppliers->count()}");
        }

        return response()->json(['success' => true, 'data' => $suppliers]);
    }

    /**
     * Get all ancestor category IDs (recursive) for a specific category.
     * Includes the provided category ID in the returned set when $includeSelf is true.
     */
    private function getAllAncestorCategoryIds(int $categoryId, bool $includeSelf = true): array
    {
        $ancestors = $includeSelf ? [$categoryId] : [];
        $currentId = $categoryId;

        // Safety limit to prevent infinite loops in case of circular references
        $maxDepth = 20;
        $depth = 0;

        while ($depth < $maxDepth) {
            $parent = DB::table('t_ItemCategories')
                ->where('Id', $currentId)
                ->value('ParentId');

            if (empty($parent) || $parent == 0) {
                break;
            }

            $ancestors[] = (int)$parent;
            $currentId = $parent;
            $depth++;
        }

        return array_unique($ancestors);
    }

    // Return allowed ItemType IDs for a tender category (FK Id)
    private function allowedItemTypeIdsForTender(int $tenderCategoryId): array
    {
        if (!$tenderCategoryId) return [];

        // **FIX: Map results to integers**
        $ids = DB::table('t_TenderCategoryItemTypes')
            ->where('TenderCategoryId', $tenderCategoryId)
            ->where('IsActive', 1)
            ->pluck('ItemTypeId')
            ->map(fn($id) => (int)$id)  // Ensure integers
            ->toArray();

        // Log::info("Allowed ItemType IDs for tender category {$tenderCategoryId}", ['type_ids' => $ids]);

        if (empty($ids)) {
            $label = \App\Models\Procurement\TenderCategory::where('Id', $tenderCategoryId)->value('TenderCategory');
            $types = DB::table('t_ItemTypes')
                ->join('t_CodeDetails', 't_ItemTypes.TypeName', '=', 't_CodeDetails.Id')
                ->pluck('t_ItemTypes.Id', 't_CodeDetails.Description')
                ->map(fn($id) => (int)$id);  // Ensure integers here too

            return match (strtoupper((string)$label)) {
                'GOODS' => array_values(array_filter([
                    $types['Stock'] ?? null,
                    $types['Consumable'] ?? null,
                    $types['Asset'] ?? null,
                ])),
                'SERVICES' => array_values(array_filter([
                    $types['Services'] ?? null,
                    $types['Intangibles'] ?? null,
                ])),
                'WORKS' => array_values(array_filter([
                    $types['Services'] ?? null,
                ])),
                default => [],
            };
        }
        return $ids;
    }
    // Validate an item is within tender's top-level category and allowed item types
    // Replace your isItemAllowedForTender method with this debug version:
    // Replace your existing isItemAllowedForTender method with this version
    // Validate an item is within tender's top-level category and allowed item types
    private function isItemAllowedForTender(int $itemId, int $tenderTopCategoryId, array $allowedTypeIds): bool
    {
        $item = ItemMasterList::select('Id', 'Category', 'ItemType')->find($itemId);

        if (!$item) {
            Log::error("Item not found", ['item_id' => $itemId]);
            return false;
        }

        // t_Items.ItemType is FK to t_ItemTypes.Id (e.g., 9 for Stock)
        // allowedTypeIds contains t_ItemTypes.Id values allowed for this Tender Category

        $itemTypeId = (int)$item->ItemType;
        $allowedTypeIds = array_map('intval', $allowedTypeIds);

        // Log::info("Checking item eligibility", [
        //     'item_id' => $itemId,
        //     'item_type_id' => $itemTypeId,
        //     'allowed_type_ids' => $allowedTypeIds
        // ]);

        if (!empty($allowedTypeIds) && !in_array($itemTypeId, $allowedTypeIds, true)) {
            Log::warning("Item rejected: type not allowed", [
                'item_id' => $itemId,
                'item_type_id' => $itemTypeId,
                'allowed_type_ids' => $allowedTypeIds
            ]);
            return false;
        }

        return true;
    }


    private function resolveTopLevelCategoryId(?int $categoryId): ?int
    {
        // Resolve root ancestor, treating ParentId NULL or 0 as root across environments
        if (!$categoryId) return null;
        $seen = [];
        $current = (int)$categoryId;
        while (true) {
            if ($current === 0) {
                // hit a 0 parent marker; cannot go higher — best known is previous
                return $seen ? (int)end($seen) : (int)$categoryId;
            }
            if (in_array($current, $seen, true)) {
                // cycle protection
                return (int)$current;
            }
            $seen[] = $current;
            $row = ItemCategories::select('Id', 'ParentId')->find($current);
            if (!$row) {
                // missing link; return last known good
                return (int)($seen[count($seen) - 1] ?? $categoryId);
            }
            $parent = $row->ParentId;
            if ($parent === null || (int)$parent === 0) {
                return (int)$row->Id;
            }
            $current = (int)$parent;
        }
    }

    /**
     * Get all descendant category IDs (recursive) for one or more parent categories.
     * Includes the provided category IDs in the returned set when $includeSelf is true.
     */
    private function getAllDescendantCategoryIds(array|int $categoryIds, bool $includeSelf = true): array
    {
        $start = array_values(array_unique(array_map('intval', is_array($categoryIds) ? $categoryIds : [$categoryIds])));
        if (empty($start)) return [];

        $all = $includeSelf ? $start : [];
        $queue = collect($start);
        while ($queue->isNotEmpty()) {
            $batch = $queue->splice(0, 200)->all();
            $children = DB::table('t_ItemCategories')->whereIn('ParentId', $batch)->pluck('Id');
            $new = $children->diff($all);
            if ($new->isNotEmpty()) {
                $ids = $new->values()->all();
                $all = array_values(array_unique(array_merge($all, $ids)));
                $queue = $queue->merge($ids);
            }
        }
        return $all;
    }

    /**
     * Show workflow history for a tender
     */
    public function workflowHistory($id)
    {
        try {
            $tender = Tender::with([
                'currency',
                'procurementMode',
            ])->findOrFail($id);

            // Get workflow status using the service method
            $workflowStatus = $this->workflow->getStatus($tender);

            // Get full workflow history with relationships
            $history = $tender->workflowHistory()
                ->with(['creator', 'status', 'stage', 'modifier'])
                ->orderBy('CreatedOn', 'desc')
                ->get();

            // Extract workflow information
            $hasWorkflow = $workflowStatus['hasWorkflow'] ?? false;
            $currentStage = $workflowStatus['currentStage'] ?? null;
            $pendingApprovers = collect($workflowStatus['pendingApprovers'] ?? []);
            $completedApprovals = collect($workflowStatus['completedApprovals'] ?? []);
            $totalPending = $workflowStatus['totalPending'] ?? 0;
            $totalCompleted = $workflowStatus['totalCompleted'] ?? 0;

            // Get next stage information
            $nextStage = null;
            $nextStageApprovers = collect();

            if ($currentStage) {
                $nextStage = \App\Models\Core\Approval\WorkflowStage::where('Order', '>', $currentStage['order'])
                    ->orderBy('Order', 'asc')
                    ->first();

                if ($nextStage) {
                    $nextStageApprovers = DB::table('t_WorkflowPending as wa')
                        ->join('t_Users as u', 'wa.UserId', '=', 'u.Id')
                        ->where('wa.Stage', $nextStage->Id)
                        ->whereNull('wa.DeletedOn')
                        ->whereNull('u.DeletedOn')
                        ->select('u.Id', 'u.Name as Name', 'u.Email')
                        ->get();
                }
            }

            // Debug logging
            // \Log::info('Workflow History Debug', [
            //     'tender_id' => $tender->Id,
            //     'has_workflow' => $hasWorkflow,
            //     'history_count' => $history->count(),
            //     'current_stage' => $currentStage,
            //     'pending_count' => $totalPending,
            //     'completed_count' => $totalCompleted,
            //     'next_stage' => $nextStage?->StageName ?? 'None',
            // ]);


            // Fetch additional workflow details
            $approvalType = 'N/A';
            $approversNeeded = 0;
            $fullCurrentStage = null;

            if ($currentStage && isset($currentStage['id'])) {
                $fullCurrentStage = \App\Models\Core\Approval\WorkflowStage::find($currentStage['id']);
                if ($fullCurrentStage) {
                    $approversNeeded = $fullCurrentStage->Count ?? 0;

                    // Fetch Workflow Type from Stage directly (since t_WorkflowStages has WorkFlowTypeId)
                    if (!empty($fullCurrentStage->WorkFlowTypeId)) {
                        $typeModel = \App\Models\Settings\WorkFlowType::find($fullCurrentStage->WorkFlowTypeId);
                        if ($typeModel) {
                            $approvalType = $typeModel->Name;
                        }
                    }

                    // Fallback to Workflow relationship if Stage doesn't have it (legacy check)
                    if ($approvalType === 'N/A') {
                        $workflow = $fullCurrentStage->workflow;
                        if ($workflow && $workflow->relationLoaded('type_name') && $workflow->type_name) {
                            $approvalType = $workflow->type_name->Name ?? 'N/A';
                        }
                    }
                }
            }
            // Fallback: if no current stage, try to get workflow from history or tender
            if ($approvalType === 'N/A' && $history->count() > 0) {
                // Try to guess or fetch from first history item stage
                $firstItem = $history->first();
                if ($firstItem && $firstItem->stage) {
                    $workflow = $firstItem->stage->workflow;
                    if ($workflow) {
                        $approvalType = $workflow->type_name->Name ?? 'N/A';
                    }
                }
            }

            return view('procurement.tendering.initiatetender.workflow-history', compact(
                'tender',
                'history',
                'hasWorkflow',
                'currentStage',
                'pendingApprovers',
                'completedApprovals',
                'totalPending',
                'totalCompleted',
                'nextStage',
                'nextStageApprovers',
                'approvalType',
                'approversNeeded'
            ));
        } catch (\Exception $e) {
            Log::error('Failed to load workflow history: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to load workflow history: ' . $e->getMessage());
        }
    }

    private function createTenderModel(Request $request): Tender
    {
        return Tender::create([
            'TenderNo' => 'TNDR-' . Str::upper(Str::random(8)),
            'Title' => $request->title,
            'TenderType' => TenderTypeEnum::from($request->tender_type),
            'TenderCategory' => $request->tender_category_id,
            'ScopeOfWork' => $request->scope_of_work,
            'Instructions' => $request->instructions,
            'SubmissionDeadline' => $request->submission_deadline,
            'OpeningDate' => $request->opening_date,
            'Status' => TenderStatusEnum::Draft,  // Use enum instead of string
            'ApprovalStatus' => null,  // Set to null for new drafts
            'ItemCategoryId' => $request->item_category_id,
            'CurrencyId' => $request->currency_id,
            'CreatedBy' => Auth::id(),
            'ModifiedBy' => Auth::id(),
        ]);
    }
    private function createTenderItems(Request $request, Tender $tender): void
    {
        if (!empty($request->plan_items)) {
            $allowedTypeIds = $this->allowedItemTypeIdsForTender((int)$tender->TenderCategory);
            foreach ($request->plan_items as $compositeKey => $item) {
                $split = explode('-', $compositeKey);
                $planItemId = (int)end($split);
                $itemId = $item['item_id'] ?? null;
                if ($itemId && !$this->isItemAllowedForTender((int)$itemId, (int)$tender->ItemCategoryId, $allowedTypeIds)) {
                    Log::warning("Plan item $itemId rejected for tender due to category/type mismatch");
                    continue;
                }
                TenderItems::create([
                    'TenderID' => $tender->Id,
                    'SourceType' => 'PLAN',
                    'ItemID' => $itemId,
                    'PlanItemID' => $planItemId,
                    'PlannedQty' => $item['qty'],
                    'QtyToTender' => $item['qty'],
                    'ItemCategory' => $request->item_category_id,
                    'RelatedPRID' => $item['pr_ref'] ?? null,
                    'CreatedBy' => Auth::id(),
                    'ModifiedBy' => Auth::id(),
                ]);
            }
        }

        if (!empty($request->manual_items)) {
            $allowedTypeIds = $this->allowedItemTypeIdsForTender((int)$tender->TenderCategory);
            foreach ($request->manual_items as $manualItem) {
                if (empty($manualItem['item_id'])) {
                    continue;
                }
                if (!$this->isItemAllowedForTender((int)$manualItem['item_id'], (int)$tender->ItemCategoryId, $allowedTypeIds)) {
                    Log::warning("Manual item {$manualItem['item_id']} rejected for tender due to category/type mismatch");
                    continue;
                }
                TenderItems::create([
                    'TenderID' => $tender->Id,
                    'SourceType' => 'MANUAL',
                    'ItemID' => $manualItem['item_id'] ?? null,
                    'QtyToTender' => $manualItem['qty'],
                    'ItemCategory' => $request->item_category_id,
                    'RelatedPRID' => $manualItem['pr_ref'] ?? null,
                    'CreatedBy' => Auth::id(),
                    'ModifiedBy' => Auth::id(),
                ]);
            }
        }
    }

    private function createTenderSuppliers(Request $request, Tender $tender): void
    {
        if (!empty($request->suppliers)) {
            foreach ($request->suppliers as $supplierId) {
                TenderSupplier::create([
                    'TenderID' => $tender->Id,
                    'SupplierID' => $supplierId,
                    'CreatedBy' => Auth::id(),
                    'ModifiedBy' => Auth::id(),
                ]);
            }
        }
    }

    private function attachDocuments(Request $request, Tender $tender): void
    {
        if (!$request->hasFile('documents')) {
            return;
        }

        $uploadedCount = 0;
        $failedCount = 0;

        foreach ((array) $request->file('documents') as $uploadedFile) {
            if (!$uploadedFile || !$uploadedFile->isValid()) {
                Log::warning('Invalid file upload detected', [
                    'tender_id' => $tender->Id,
                    'file' => $uploadedFile ? $uploadedFile->getClientOriginalName() : 'null'
                ]);
                continue;
            }

            try {
                // Log::info('Attempting to attach document', [
                //     'tender_id' => $tender->Id,
                //     'filename' => $uploadedFile->getClientOriginalName(),
                //     'size' => $uploadedFile->getSize(),
                //     'mime' => $uploadedFile->getMimeType()
                // ]);

                $test = $tender->newDocument(
                    ModulesEnum::Procurement,
                    $uploadedFile,
                    [PermissionEnum::TenderWrite->value],
                    Auth::user()
                );

                $uploadedCount++;
                // Log::info('Document attached successfully', [
                //     'tender_id' => $tender->Id,
                //     'filename' => $uploadedFile->getClientOriginalName(),
                //     'full_log' => $test
                // ]);
            } catch (\Exception $e) {
                $failedCount++;
                Log::error('Failed to attach document to tender', [
                    'tender_id' => $tender->Id,
                    'filename' => $uploadedFile->getClientOriginalName(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                // Don't throw - allow tender creation to continue
                // You could throw here if documents are critical:
                // throw new \Exception("Failed to attach document: " . $e->getMessage());
            }
        }

        // Log::info('Document attachment completed', [
        //     'tender_id' => $tender->Id,
        //     'uploaded' => $uploadedCount,
        //     'failed' => $failedCount
        // ]);
    }

    private function initiateWorkflow(Tender $tender): void
    {
        try {
            $this->workflow->submit($tender, Auth::user(), TenderApprovalStatusEnum::PENDING, 'Tender submitted for approval');
            activity()
                ->performedOn($tender)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'submit for approval'])
                ->log('Tender submitted for approval with ID: ' . $tender->Id);
        } catch (Throwable $wfEx) {
            Log::error('Failed to initiate workflow for Tender ID ' . $tender->Id . ': ' . $wfEx->getMessage());
            throw new Exception('Failed to initiate approval workflow. Please contact the system administrator.');
        }
    }

    private function sendSupplierNotifications(Request $request, Tender $tender): void
    {
        try {
            $selectedSupplierIds = collect($request->suppliers ?? [])->map(fn($v) => (int)$v)->unique()->values()->all();

            if (empty($selectedSupplierIds)) {
                return;
            }

            $thirdPartyUserEmailSub = DB::table('t_ThirdPartyUsers as tpu')
                ->select('tpu.ThirdPartyId', DB::raw('MIN(tpu.Email) as Email'))
                ->whereNull('tpu.DeletedOn')
                ->groupBy('tpu.ThirdPartyId');

            $recipientRows = DB::table('t_Suppliers as s')
                ->join('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyID')
                ->leftJoinSub($thirdPartyUserEmailSub, 'tpu', function ($join) {
                    $join->on('tpu.ThirdPartyId', '=', 'tp.Id');
                })
                ->whereIn('s.Id', $selectedSupplierIds)
                ->whereNull('s.DeletedOn')
                ->whereNull('tp.DeletedOn')
                ->select('tp.TradingName', DB::raw('tpu.Email as Email'))
                ->get();

            $supplierList = [];
            $usedEmails = [];
            foreach ($recipientRows as $row) {
                $email = trim((string)$row->Email);
                if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && !in_array(strtolower($email), $usedEmails, true)) {
                    $supplierList[] = ['name' => $row->TradingName, 'email' => $email];
                    $usedEmails[] = strtolower($email);
                }
            }

            if (!empty($supplierList)) {
                $this->sendEmailsToSuppliers($tender, $supplierList);
            }
        } catch (Throwable $mailEx) {
            Log::error('Failed sending tender notifications: ' . $mailEx->getMessage());
        }
    }

    private function sendEmailsToSuppliers(Tender $tender, array $supplierList): void
    {
        $actor = Auth::user();
        $subject = 'New Tender Published: ' . ($tender->TenderNo ?? 'Tender');
        $submissionFormatted = $tender->SubmissionDeadline ? (string)$tender->SubmissionDeadline : 'N/A';
        $openingFormatted = $tender->OpeningDate ? (string)$tender->OpeningDate : 'N/A';

        $bodyTemplate = '<p>A new tender has been created with the following details:</p>' .
            '<ul>' .
            '<li><strong>Tender No:</strong> ' . e($tender->TenderNo) . '</li>' .
            '<li><strong>Title:</strong> ' . e($tender->Title) . '</li>' .
            '<li><strong>Submission Deadline:</strong> ' . e($submissionFormatted) . '</li>' .
            '<li><strong>Opening Date:</strong> ' . e($openingFormatted) . '</li>' .
            '<li><strong>Scope:</strong> ' . e($tender->ScopeOfWork ?? 'N/A') . '</li>' .
            '<li><strong>Instructions:</strong> ' . e($tender->Instructions ?? 'N/A') . '</li>' .
            '</ul>' .
            '<p>Please log in to the procurement portal to view full details and respond accordingly.</p>';

        $uniqueEmails = array_values(array_unique(array_map(fn($s) => strtolower($s['email']), $supplierList)));
        foreach ($uniqueEmails as $recipientEmail) {
            $recipientName = null;
            foreach ($supplierList as $s) {
                if (strtolower($s['email']) === $recipientEmail) {
                    $recipientName = $s['name'];
                    break;
                }
            }

            $to = [[$recipientName ?? $recipientEmail => $recipientEmail]];
            $salutation = $recipientName ?? $recipientEmail;
            $personalBody = '<p>Hello ' . e($salutation) . ',</p>' . $bodyTemplate;

            \App\Services\CRMEmailService::createRaw($actor, $subject, $personalBody, $to, 'ThirdParty', '', [], [], EmailPriorityEnum::Important)->send(true);
        }
    }

    /**
     * Send invitations to selected suppliers for restricted tenders
     */
    private function sendRestrictedTenderInvitations(Tender $tender): int
    {
        $invitationsSent = 0;

        try {
            // Get all selected suppliers for this tender from t_TenderSuppliers
            $selectedSuppliers = TenderSupplier::where('TenderID', $tender->Id)
                ->with(['supplier.party'])
                ->get();

            if ($selectedSuppliers->isEmpty()) {
                Log::warning("No suppliers found for restricted tender ID: {$tender->Id}");
                return 0;
            }

            foreach ($selectedSuppliers as $tenderSupplier) {
                if (!$tenderSupplier->supplier || !$tenderSupplier->supplier->party) {
                    Log::warning("Missing supplier or party data for TenderSupplier ID: {$tenderSupplier->id}");
                    continue;
                }

                $masterSupplier = $tenderSupplier->supplier;
                $thirdParty = $masterSupplier->party;

                // Skip if no email address
                if (!$thirdParty->Email) {
                    Log::warning("No email address for supplier {$thirdParty->ThirdPartyName} (ID: {$masterSupplier->Id})");
                    continue;
                }

                // Resolve valid valid SupplierId for t_TenderInvitations (FK to t_Suppliers)
                $validSupplierCategory = \App\Models\ThirdParies\Supplier::where('SupplierMasterId', $masterSupplier->Id)
                    ->where('Active_Status', 1)
                    ->when($tender->Category, function ($q) use ($tender) {
                        return $q->where('CategoryId', $tender->Category);
                    })
                    ->first();

                // Fallback: If no category-specific match, take ANY active supplier record for this master
                if (!$validSupplierCategory) {
                    $validSupplierCategory = \App\Models\ThirdParies\Supplier::where('SupplierMasterId', $masterSupplier->Id)->first();
                }

                if (!$validSupplierCategory) {
                    Log::error("Cannot send invitation to Supplier Master ID {$masterSupplier->Id}: No enabling record found in t_Suppliers.");
                    continue;
                }

                // Create invitation record in t_TenderInvitations
                $invitation = TenderInvitation::create([
                    'TenderId' => $tender->Id,
                    'SupplierId' => $validSupplierCategory->Id,
                    'InvitationDate' => now(),
                    'ResponseStatus' => TenderInvitation::STATUS_PENDING,
                    'ResponseDate' => null,
                    'DeclineReason' => null,
                    'ConfirmationAttachment' => null,
                    'CreatedBy' => Auth::id(),
                    'CreatedOn' => now(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);

                // Send email invitation
                // Send email invitation
                try {
                    // Refactor: Use CRMEmailService standard
                    $subject = 'Tender Invitation - ' . $tender->TenderNo . ': ' . $tender->Title;

                    // Render view to string
                    $body = view('emails.tender-invitation', [
                        'tender' => $tender,
                        'supplier' => $validSupplierCategory,
                        'supplierName' => $validSupplierCategory->thirdParty->ThirdPartyName ?? 'Valued Supplier',
                        'submissionDeadline' => $tender->SubmissionDeadline,
                        'portalUrl' => config('app.url') . '/supplier/tenders/' . $tender->Id,
                    ])->render();

                    // Create and send email via CRMEmailService
                    $actor = Auth::user() ?? \App\Models\Auth\User::find(1); // Fallback to Admin if system process

                    \App\Services\CRMEmailService::createRaw(
                        $actor,
                        $subject,
                        $body,
                        [['Name' => $thirdParty->ThirdPartyName, 'Email' => $thirdParty->Email]], // To
                        'ThirdParty', // Party Type
                        $thirdParty->Id // Party ID
                    )->send();

                    $invitationsSent++;

                    // Log::info("Tender invitation sent via CRMEmailService to {$thirdParty->ThirdPartyName} ({$thirdParty->Email}) for tender {$tender->TenderNo}");
                } catch (Exception $emailException) {
                    Log::error("Failed to send email to {$thirdParty->Email}: " . $emailException->getMessage());

                    // Update invitation record to indicate email failure (but keep the record)
                    $invitation->update([
                        'DeclineReason' => 'Email sending failed: ' . $emailException->getMessage(),
                        'ModifiedOn' => now(),
                    ]);
                }
            }

            // Log::info("Restricted tender invitations process completed. Total sent: {$invitationsSent}");
        } catch (Exception $e) {
            Log::error("Error in sendRestrictedTenderInvitations: " . $e->getMessage());
            throw $e; // Re-throw to be caught by the main transaction
        }

        return $invitationsSent;
    }
}
