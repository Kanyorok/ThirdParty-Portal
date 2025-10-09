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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Throwable;
use App\Models\Procurement\TenderInvitation;
use App\Mail\TenderInvitation as TenderInvitationMail;
use Illuminate\Support\Facades\Mail;

class TenderController extends Controller
{
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
        $procurementPlan = ConsolidatedProcurementPlan::select('PlanID', 'ReferenceNumber', 'Title')
            ->where('Status', ProcurementPlanStatusEnum::Approved)
            ->get();

        $allCategories = ItemCategories::select('Id', 'ParentId')->get()->keyBy('Id');
        $categoryToTopLevel = [];
        foreach ($allCategories as $category) {
            $current = $category;
            while ($current->ParentId !== null && isset($allCategories[$current->ParentId])) {
                $current = $allCategories[$current->ParentId];
            }
            $categoryToTopLevel[$category->Id] = $current->Id;
        }

        $allItemsWithCategoryIds = ItemMasterList::select('Id', 'ItemName', 'Category')->get()->map(function ($item) use ($categoryToTopLevel) {
            return [
                'Id' => $item->Id,
                'ItemName' => $item->ItemName,
                'Category' => $categoryToTopLevel[$item->Category] ?? $item->Category,
            ];
        });

        Log::info('All Items With Category IDs count: ' . $allItemsWithCategoryIds->count());
        if ($allItemsWithCategoryIds->count() > 0) {
            Log::info('Sample Item: ' . json_encode($allItemsWithCategoryIds->first()));
        }

        $procurementPlans = ConsolidatedProcurementPlan::select('PlanID', 'ReferenceNumber', 'Title')
            ->where('Status', ProcurementPlanStatusEnum::Approved)
            ->get()
            ->keyBy('PlanID');

        // Only include plan line items that:
        // - belong to approved plans
        // - have procurement method set to a Tender (Description contains 'Tender')
        // - have NOT already been added to a tender (no TenderItems with this PlanItemID)
        $approvedPlanIds = $procurementPlans->keys();
        $usedPlanItemIds = TenderItems::whereNotNull('PlanItemID')->pluck('PlanItemID');

        $itemsCategories = PlanLineItem::select('LineItemID', 'PlanID', 'ItemID', 'MergedQty', 'BranchID', 'DepartmentID', 'ProcurementMethod')
            ->with([
                'item' => function ($query) { $query->select('Id', 'ItemName'); },
                'procurementMode',
                'departmentNeed' => function ($q) { $q->select('NeedID', 'ItemID', 'BranchID', 'DepartmentID'); }
            ])
            ->whereIn('PlanID', $approvedPlanIds)
            ->whereNotIn('LineItemID', $usedPlanItemIds)
            ->whereHas('procurementMode', function ($q) {
                $q->where('Description', 'like', '%Tender%');
            })
            ->get();

        $procurementPlansOutput = [];
        $planItemData = [];
        foreach ($itemsCategories as $lineItem) {
            $planId = $lineItem->PlanID;
            $item = $lineItem->item;
            if (!$item) { continue; }
            $needId = optional($lineItem->departmentNeed)->NeedID;

            $entry = [
                'id' => $planId,
                'planLineItemId' => $lineItem->LineItemID,
                'itemId' => $item->Id,
                'name' => $item->ItemName,
                'plannedQty' => $lineItem->MergedQty,
                'needId' => $needId,
            ];
            $procurementPlansOutput[$planId][] = $entry;
            $planItemData[$planId][] = $entry;
        }

        // Get suppliers with their supplier categories and item categories mapping
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
        DB::beginTransaction();

        try {
            $tender = new Tender();

            $tender->TenderNo = 'TNDR-' . Str::upper(Str::random(8));
            $tender->Title = $request->title;
            $tender->TenderType = TenderTypeEnum::from($request->tender_type);
            $tender->TenderCategory = $request->tender_category_id;
            $tender->ScopeOfWork = $request->scope_of_work;
            $tender->Instructions = $request->instructions;
            $tender->SubmissionDeadline = $request->submission_deadline;
            $tender->OpeningDate = $request->opening_date;
            $tender->Status = 'dr';
            $tender->ItemCategoryId = $request->item_category_id;
            $tender->CreatedBy = Auth::id();
            $tender->CreatedOn = now();
            $tender->ModifiedBy = Auth::id();
            $tender->ModifiedOn = now();
            $tender->CurrencyId = $request->currency_id;
            $tender->save();

            $tenderId = $tender->Id;
            if (!empty($request->plan_items)) {
                foreach ($request->plan_items as $compositeKey => $item) {
                    $split = explode('-', $compositeKey);
                    $planItemId = (int)end($split);
                    TenderItems::create([
                        'TenderID' => $tenderId,
                        'SourceType' => 'PLAN',
                        'ItemID' => $item['item_id'] ?? null,
                        'PlanItemID' => $planItemId,
                        'PlannedQty' => $item['qty'],
                        'QtyToTender' => $item['qty'],
                        'ItemCategory' => $request->item_category_id,
                        'Remarks' => null,
                        'RelatedPRID' => $item['pr_ref'] ?? null,
                        'CreatedBy' => Auth::id(),
                        'ModifiedBy' => Auth::id(),
                    ]);
                }
            }

            if (!empty($request->manual_items)) {
                foreach ($request->manual_items as $manualItem) {
                    if (empty($manualItem['item_id'])) {
                        // continue;
                    } else {
                        TenderItems::create([
                            'TenderID' => $tenderId,
                            'SourceType' => 'MANUAL',
                            'ItemID' => $manualItem['item_id'] ?? null,
                            'ManualItemDescription' => null,
                            'PlannedQty' => null,
                            'QtyToTender' => $manualItem['qty'],
                            'ItemCategory' => $request->item_category_id,
                            'Remarks' => null,
                            'RelatedPRID' => $manualItem['pr_ref'] ?? null,
                            'CreatedBy' => Auth::id(),
                            'ModifiedBy' => Auth::id(),
                        ]);
                    }
                }
            }

            if (!empty($request->suppliers)) {
                foreach ($request->suppliers as $supplierId) {
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
            return redirect()->route('initiatetender.index')->with('success', 'Tender created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $e->getMessage();
            Log::error("--- CREATE TENDER ERROR --- " . $e->getMessage());
            Log::error($e);
            return redirect()->route('initiatetender.index')->with('error', 'Failed to create Tender. Please try again.');
        }
    }

    public function show(string $id)
    {
        $this->authorize(PermissionEnum::TenderRead, Tender::class);
        $tender = Tender::findOrFail($id);
        $show = false;
        if ($tender->ApprovalStatus === TenderApprovalStatusEnum::REJECTED || $tender->ApprovalStatus === TenderApprovalStatusEnum::APPROVED) {
            $show = true;
        }
        $items = TenderItems::where('TenderID', $id)->with(['item', 'category', 'item.price'])->get();

        $totalEstimatedCost = $items->sum(function ($item) {
            $qty = $item->QtyToTender ?? 0;
            $price = $item->item?->price?->ActualPrice ?? 0;
            return $qty * $price;
        });

        $suppliers = TenderSupplier::where('TenderID', $id)->with('supplier.thirdParty')->get();
        $tenderCategory = TenderCategory::find($tender->tender_category_id);
        $itemCategory = ItemCategories::find($tender->item_category_id);
        $currency = Currency::find($tender->currency_id);
        $procurementPlan = ProcurementPlan::find($tender->procurement_plan_id);

        $planItems = [];
        $manualItems = [];

        foreach ($planItems as $planItem) {
            $planItem->item_name = ItemMasterList::find($planItem->item_id)?->ItemName ?? 'N/A';
        }

        foreach ($manualItems as $manualItem) {
            $manualItem->item_name = ItemMasterList::find($manualItem->item_id)?->ItemName ?? 'N/A';
        }

        return view('procurement.tendering.tendersetup.tenderinitiation.show', compact(
            'tender',
            'tenderCategory',
            'itemCategory',
            'currency',
            'procurementPlan',
            'planItems',
            'manualItems',
            'items',
            'suppliers',
            'show',
            'totalEstimatedCost'
        ));
    }

    public function edit(string $id)
    {
        $this->authorize(PermissionEnum::TenderUpdate, Tender::class);
        $tender = Tender::findOrFail($id);
        $show = false;
        if ($tender->ApprovalStatus === TenderApprovalStatusEnum::REJECTED || $tender->ApprovalStatus === TenderApprovalStatusEnum::APPROVED) {
            $show = true;
        }
        $items = TenderItems::where('TenderID', $id)->where('ItemCategory', $tender->ItemCategoryId)->get();
        $otherItemsForThatTender = ItemMasterList::where('Category', $tender->ItemCategoryId)->get();
        $suppliers = TenderSupplier::where('TenderID', $id)->with('supplier')->get();
        $existingSupplierIds = $suppliers->pluck('SupplierID')->toArray();
        $otherSuppliers = Supplier::select('Id', 'ThirdPartyName', 'CategoryId', 'ContactPhone', 'ContactEmail')
            ->where('CategoryId', $tender->ItemCategoryId)
            ->whereNotIn('Id', $existingSupplierIds)
            ->get();
        $tenderCategory = TenderCategory::find($tender->TenderCategory)->Id;
        $tenderCategories = TenderCategory::select('Id', 'TenderCategory')->get();
        $itemCategory = ItemCategories::find($tender->ItemCategoryId)->Name;
        $itemCategoryID = ItemCategories::find($tender->ItemCategoryId)->Id;
        $currency = $tender->CurrencyId;

        $allCurrency = Currency::select('Id', 'Name', 'Code', 'Symbol')->get();
        $procurementPlan = ProcurementPlan::find($tender->procurement_plan_id);

        $planItems = [];
        $manualItems = [];

        foreach ($planItems as $planItem) {
            $planItem->item_name = ItemMasterList::find($planItem->item_id)?->ItemName ?? 'N/A';
        }

        foreach ($manualItems as $manualItem) {
            $manualItem->item_name = ItemMasterList::find($manualItem->item_id)?->ItemName ?? 'N/A';
        }

        return view('procurement.tendering.tendersetup.tenderinitiation.edit', compact(
            'tender',
            'tenderCategory',
            'tenderCategories',
            'itemCategory',
            'itemCategoryID',
            'currency',
            'procurementPlan',
            'planItems',
            'manualItems',
            'items',
            'otherItemsForThatTender',
            'suppliers',
            'otherSuppliers',
            'show',
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
                'documents.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:2048',
            ]);

            DB::beginTransaction();

            try {
                $tender = Tender::findOrFail($id);
                $tender->Title = $validated['title'];
                $tender->TenderCategory = $validated['tender_category_id'];
                $tender->CurrencyId = $validated['currency_id'];
                $tender->SubmissionDeadline = $validated['submission_deadline'];
                $tender->OpeningDate = $validated['opening_date'];

                $tender->save();

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
                Log::error($e);
                return redirect()->route('initiatetender.edit', $id)->with('error', 'Failed to update Tender. Please try again.');
            }
        } elseif ($type == 'crudItem') {
            $crudType = $request->crudType;
            if ($crudType == 'addItem') {
                $validated = $request->validate([
                    'item_id' => 'required|integer',
                    'QtyToTender' => 'required|integer|min:1',
                    'pr_ref' => 'nullable|string|max:255',
                ]);
                DB::beginTransaction();
                try {
                    $tenderItem = new TenderItems();
                    $tenderItem->TenderID = $id;
                    $tenderItem->SourceType = 'MANUAL';
                    $tenderItem->ItemID = $validated['item_id'];
                    $tenderItem->itemCategory = $request->itemCategoryID;
                    $tenderItem->QtyToTender = $validated['QtyToTender'];
                    $tenderItem->RelatedPRID = $validated['pr_ref'] ?? null;
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
            $tender->TenderCategory = TenderCategoryEnum::from($request->TenderCategory);
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
        
        // Validate the request
        $request->validate([
            'tender_id' => 'required|exists:t_Tenders,Id',
            'reason' => 'required|string|max:1000',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Find the tender
            $tender = Tender::findOrFail($request->tender_id);
            
            // Update the approval status and related fields
            $tender->update([
                'ApprovalStatus' => TenderApprovalStatusEnum::APPROVED,
                'ApprovalRemarks' => $request->reason,
                'Status' => TenderStatusEnum::Published, // Approve and publish as mentioned in the modal
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);
            
            // Handle restricted tender invitations
            $invitationsSent = 0;
            if ($tender->isRestricted()) {
                $invitationsSent = $this->sendRestrictedTenderInvitations($tender);
            }
            
            // Log the approval activity
            activity()
                ->performedOn($tender)
                ->causedBy(Auth::user())
                ->withProperties([
                    'action' => 'approve',
                    'approval_remarks' => $request->reason,
                    'previous_status' => 'Pending',
                    'new_status' => 'Approved',
                    'tender_type' => $tender->TenderType->value,
                    'invitations_sent' => $invitationsSent
                ])
                ->log('Tender approved and published with ID: ' . $tender->Id . ($invitationsSent > 0 ? ". Invitations sent to {$invitationsSent} suppliers." : ''));
            
            DB::commit();
            
            $successMessage = 'Tender approved and published successfully.';
            if ($invitationsSent > 0) {
                $successMessage .= " Invitations sent to {$invitationsSent} suppliers.";
            }
            
            return redirect()->route('initiatetender.index')->with('success', $successMessage);
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error("--- APPROVE TENDER ERROR --- " . $th->getMessage());
            Log::error($th);
            return redirect()->route('initiatetender.index')->with('error', 'Failed to approve Tender. Please try again.');
        }
    }

    public function rejectTender(Request $request)
    {
        $this->authorize(PermissionEnum::TenderUpdate, Tender::class);
        
        // Validate the request
        $request->validate([
            'tender_id' => 'required|exists:t_Tenders,Id',
            'reason' => 'required|string|max:1000',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Find the tender
            $tender = Tender::findOrFail($request->tender_id);
            
            // Update the approval status and related fields
            $tender->update([
                'ApprovalStatus' => TenderApprovalStatusEnum::REJECTED,
                'ApprovalRemarks' => $request->reason,
                'Status' => TenderStatusEnum::Draft, // Keep as draft when rejected
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);
            
            // Log the rejection activity
            activity()
                ->performedOn($tender)
                ->causedBy(Auth::user())
                ->withProperties([
                    'action' => 'reject',
                    'rejection_remarks' => $request->reason,
                    'previous_status' => 'Pending',
                    'new_status' => 'Rejected'
                ])
                ->log('Tender rejected with ID: ' . $tender->Id);
            
            DB::commit();
            
            return redirect()->route('initiatetender.index')->with('success', 'Tender rejected successfully.');
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error("--- REJECT TENDER ERROR --- " . $th->getMessage());
            Log::error($th);
            return redirect()->route('initiatetender.index')->with('error', 'Failed to reject Tender. Please try again.');
        }
    }

    /**
     * Get prequalified suppliers based on active prequalification rounds
     */
    private function getPrequalifiedSuppliers()
    {
        // Find active prequalification rounds (DB code 'O' for Open)
        $activeRounds = \App\Models\Procurement\Prequalification\PrequalificationRound::where('Status', 'O')
            ->where('StartDate', '<=', now())
            ->where('EndDate', '>=', now())
            ->pluck('RoundID');

        if ($activeRounds->isEmpty()) {
            return collect([]); // No active rounds, no suppliers available
        }

        // FIXED: Query t_Suppliers directly instead of looking for approved applications
        // and derive SupplierCategory mapping via pivot if SupplierCategoryID is null
        $prequalifiedSuppliers = \App\Models\ThirdParies\Supplier::whereIn('RoundID', $activeRounds)
            ->where('Active_Status', 1)
            ->with(['thirdParty', 'supplierCategory.itemCategories'])
            ->get();

        $suppliers = collect();
        
        foreach ($prequalifiedSuppliers as $supplier) {
            if (!$supplier->thirdParty) continue;
            
            $thirdParty = $supplier->thirdParty;
            
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
            // Add any categories from pivot t_ThirdParty_SupplierCategory (in case SupplierCategoryID is NULL)
            try {
                $pivotCats = DB::table('t_ThirdParty_SupplierCategory')
                    ->where('third_party_id', $supplier->ThirdPartyID)
                    ->pluck('SupplierCategoryID');
                $supplierCategoryIds = $supplierCategoryIds->concat($pivotCats);
            } catch (\Throwable $e) {
                Log::warning('Failed reading t_ThirdParty_SupplierCategory', ['supplierId' => $supplier->Id, 'error' => $e->getMessage()]);
            }

            $supplierCategoryIds = $supplierCategoryIds->filter()->unique()->values();

            // From all supplier categories, collect mapped item categories (including subcategories)
            if ($supplierCategoryIds->isNotEmpty()) {
                try {
                    $parentItemCats = DB::table('t_SupplierCategory_ItemCategory')
                        ->whereIn('SupplierCategoryID', $supplierCategoryIds)
                        ->whereNull('DeletedOn')
                        ->pluck('ItemCategoryID');

                    foreach ($parentItemCats as $parentCatId) {
                        $itemCategoryIds[] = (int) $parentCatId;
                        // include subcategories
                        $subcategories = \App\Models\Inventory\ItemCategories::where('ParentId', $parentCatId)->pluck('Id');
                        foreach ($subcategories as $subcategoryId) {
                            $itemCategoryIds[] = (int) $subcategoryId;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed reading category mappings', ['supplierId' => $supplier->Id, 'error' => $e->getMessage()]);
                }
            }

            $suppliers->push([
                'Id' => $supplier->Id,
                'SupplierName' => $thirdParty->ThirdPartyName,
                'ThirdPartyName' => $thirdParty->ThirdPartyName,
                'Email' => $thirdParty->Email ?? '', // Include Email for restricted tender invitations
                'CategoryId' => null, // No longer used - categories come from SupplierCategory mapping
                'SupplierCategoryID' => $supplierCategoryIds->first(), // Prefer first mapped category if any
                'ItemCategoryIds' => array_unique($itemCategoryIds), // All categories this supplier can serve
                'RoundID' => $supplier->RoundID,
                'ApplicationStatus' => 'Prequalified', // Since they're in t_Suppliers, they're prequalified
                'ThirdPartyID' => $supplier->ThirdPartyID,
            ]);
        }

        // Remove duplicates based on supplier ID (a supplier might have multiple records)
        return $suppliers->unique('Id')->values();
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
                ->with(['supplier.thirdParty'])
                ->get();

            if ($selectedSuppliers->isEmpty()) {
                Log::warning("No suppliers found for restricted tender ID: {$tender->Id}");
                return 0;
            }

            foreach ($selectedSuppliers as $tenderSupplier) {
                if (!$tenderSupplier->supplier || !$tenderSupplier->supplier->thirdParty) {
                    Log::warning("Missing supplier or thirdParty data for TenderSupplier ID: {$tenderSupplier->id}");
                    continue;
                }

                $supplier = $tenderSupplier->supplier;
                $thirdParty = $supplier->thirdParty;
                
                // Skip if no email address
                if (!$thirdParty->Email) {
                    Log::warning("No email address for supplier {$thirdParty->ThirdPartyName} (ID: {$supplier->Id})");
                    continue;
                }

                // Create invitation record in t_TenderInvitations
                $invitation = TenderInvitation::create([
                    'TenderId' => $tender->Id,
                    'SupplierId' => $supplier->Id,
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
                try {
                    Mail::to($thirdParty->Email)
                        ->send(new TenderInvitationMail($tender, $supplier));
                    
                    $invitationsSent++;
                    
                    Log::info("Tender invitation sent to {$thirdParty->ThirdPartyName} ({$thirdParty->Email}) for tender {$tender->TenderNo}");
                    
                } catch (Exception $emailException) {
                    Log::error("Failed to send email to {$thirdParty->Email}: " . $emailException->getMessage());
                    
                    // Update invitation record to indicate email failure (but keep the record)
                    $invitation->update([
                        'DeclineReason' => 'Email sending failed: ' . $emailException->getMessage(),
                        'ModifiedOn' => now(),
                    ]);
                }
            }
            
            Log::info("Restricted tender invitations process completed. Total sent: {$invitationsSent}");
            
        } catch (Exception $e) {
            Log::error("Error in sendRestrictedTenderInvitations: " . $e->getMessage());
            throw $e; // Re-throw to be caught by the main transaction
        }

        return $invitationsSent;
    }
}
