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
use App\Services\Workflow\ApprovalWorkflow;
use App\Mail\TenderInvitation as TenderInvitationMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Auth\User;
use Illuminate\Http\UploadedFile;
use App\Models\Procurement\TenderDocument;
use App\Enums\EmailPriorityEnum;
use App\Enums\Core\ModulesEnum;

class TenderController extends Controller
{

    protected ApprovalWorkflow $workflow;
    public function __construct(ApprovalWorkflow $workflow)
    {
        $this->workflow = $workflow;  // Injected with codeId via service container
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

        // Items from t_Items where DeletedOn is NULL (active only)
        $allItemsWithCategoryIds = ItemMasterList::select('Id', 'ItemName', 'Category')
            ->whereNull('DeletedOn')
            ->whereHas('price', function ($q) {
                $q->whereNotNull('ActualPrice')->where('ActualPrice', '>', 0);
            })
            ->orderBy('ItemName')
            ->get()->map(function ($item) use ($categoryToTopLevel) {
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
                'item' => function ($query) {
                    $query->select('Id', 'ItemName');
                },
                'procurementMode',
                'departmentNeed' => function ($q) {
                    $q->select('NeedID', 'ItemID', 'BranchID', 'DepartmentID');
                }
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
            if (!$item) {
                continue;
            }
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
        // Create the tender model
        $tender = $this->createTenderModel($request);
        $tenderId = $tender->Id;


        //document upload to dms
        $this->attachDocuments($request, $tender);

        // Process plan items
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
                    'TenderID' => $tenderId,
                    'SourceType' => 'PLAN',
                    'ItemID' => $itemId,
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

        // Process manual items
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

        // Process suppliers
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

        // Initialize workflow for the new tender
        try {
            $this->workflow->submit($tender, Auth::user(), TenderApprovalStatusEnum::PENDING, 'Tender submitted for approval');
            activity()
                ->performedOn($tender)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'submit for approval'])
                ->log('Tender submitted for approval with ID: ' . $tenderId);
        } catch (Throwable $wfEx) {
            Log::error('Failed to initiate workflow for Tender ID ' . $tenderId . ': ' . $wfEx->getMessage());
            throw new Exception('Failed to initiate approval workflow. Please contact the system administrator.');
        }

        // Send supplier notifications after successful creation
        try {
            $selectedSupplierIds = collect($request->suppliers ?? [])->map(fn($v) => (int)$v)->unique()->values()->all();

            if (!empty($selectedSupplierIds)) {
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
                    $actor = Auth::user();
                    $subject = 'New Tender Published: ' . ($tender->TenderNo ?? 'Tender');

                    $rawSubmission = $tender->SubmissionDeadline;
                    $submissionFormatted = $rawSubmission ? (string)$rawSubmission : 'N/A';
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

                        $bcc = [];
                        foreach ($uniqueEmails as $otherEmail) {
                            if ($otherEmail === $recipientEmail) continue;
                            $otherName = null;
                            foreach ($supplierList as $s) {
                                if (strtolower($s['email']) === $otherEmail) {
                                    $otherName = $s['name'];
                                    break;
                                }
                            }
                            $bcc[] = [$otherName ?? $otherEmail => $otherEmail];
                        }

                        $salutation = $recipientName ?? $recipientEmail;
                        $personalBody = '<p>Hello ' . e($salutation) . ',</p>' . $bodyTemplate;

                        $service = \App\Services\CRMEmailService::createRaw($actor, $subject, $personalBody, $to, 'ThirdParty', '', [], $bcc, EmailPriorityEnum::Important);
                        $service->send(true);
                    }
                }
            }
        } catch (\Throwable $mailEx) {
            Log::error('Failed sending tender notifications: ' . $mailEx->getMessage());
        }

        return redirect()->route('initiatetender.index')->with('success', 'Tender created successfully.');
    } catch (Exception $e) {
        DB::rollBack();
        Log::error("--- CREATE TENDER ERROR --- " . $e->getMessage());
        return redirect()->route('initiatetender.index')->with('error', 'Failed to create Tender. Please try again.');
    }
}


    //added maker chekcer   
   public function show(string $id)
{
    $this->authorize(PermissionEnum::TenderRead, Tender::class);
    
    $tender = Tender::findOrFail($id);
    $user = Auth::user();
    $canApprove = $this->workflow->canApproveModel($tender, $user);
    
    Log::info("User {$user->id} canApprove for Tender {$tender->Id}: " . ($canApprove ? 'YES' : 'NO'));
    
    $show = false;
    if ($tender->ApprovalStatus === TenderApprovalStatusEnum::REJECTED || 
        $tender->ApprovalStatus === TenderApprovalStatusEnum::APPROVED) {
        $show = true;
    }
    
    // Get tender items with relationships
    $items = TenderItems::where('TenderID', $id)
        ->with(['item', 'category', 'item.price'])
        ->get();

    // Calculate total estimated cost
    $totalEstimatedCost = $items->sum(function ($item) {
        $qty = $item->QtyToTender ?? 0;
        $price = $item->item?->price?->ActualPrice ?? 0;
        return $qty * $price;
    });

    // Get suppliers
    $suppliers = TenderSupplier::where('TenderID', $id)
        ->with('supplier.thirdParty')
        ->get();
    
    // FIXED: Use correct column names (PascalCase, not snake_case)
    $tenderCategory = TenderCategory::find($tender->TenderCategory); // Not tender_category_id
    $itemCategory = ItemCategories::find($tender->ItemCategoryId);    // Not item_category_id
    $currency = Currency::find($tender->CurrencyId);                  // Not currency_id
    
    // Get documents from DMS (via DocumentsTrait)
    try {
        $documents = $tender->documents; // This should work if DocumentsTrait is properly set up
    } catch (\Exception $e) {
        Log::error("Failed to load tender documents: " . $e->getMessage());
        $documents = collect(); // Return empty collection on error
    }
    
    // Note: procurement_plan_id doesn't exist in your Tender model's fillable array
    // If you need procurement plan, you should add it to the fillable array first
    // For now, I'm commenting it out:
    // $procurementPlan = ProcurementPlan::find($tender->procurement_plan_id);

    $planItems = [];
    $manualItems = [];

    // These loops don't do anything since $planItems and $manualItems are empty arrays
    // You might want to remove them or populate these arrays first
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
        // 'procurementPlan', // Commented out - column doesn't exist
        'planItems',
        'manualItems',
        'items',
        'suppliers',
        'show',
        'canApprove',
        'totalEstimatedCost',
        'documents'
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
        // Include supplier->thirdParty so we can display proper contact details
        $suppliers = TenderSupplier::where('TenderID', $id)
            ->with(['supplier.thirdParty'])
            ->get();

        $existingSupplierIds = collect($suppliers)->pluck('SupplierID')->map(fn($v) => (int)$v)->values();

        // Build addable suppliers list using the prequalification helper and filter by tender's top-level category
        $topCategoryId = (int) $tender->ItemCategoryId;
        $prequalified = $this->getPrequalifiedSuppliers(); // Collection of arrays
        $otherSuppliers = collect(value: $prequalified)
            ->filter(function ($s) use ($topCategoryId, $existingSupplierIds) {
                $id = (int) ($s['Id'] ?? 0);
                $cats = collect($s['ItemCategoryIds'] ?? []);
                return $id > 0
                    && !$existingSupplierIds->contains($id)
                    && ($topCategoryId ? $cats->contains($topCategoryId) : true);
            })
            ->map(function ($s) {
                // Normalize to object with properties expected by the view
                return (object) [
                    'Id'           => (int) ($s['Id'] ?? 0),
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
                'TenderType' => ['nullable', new Enum(TenderTypeEnum::class)],
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
                if (!empty($validated['TenderType'])) {
                    $tender->TenderType = TenderTypeEnum::from($validated['TenderType']);
                }

                $tender->save();

                // Attach Tender Documents to DMS (from edit form)
                if ($request->hasFile('documents')) {
                    foreach ((array) $request->file('documents') as $uploadedFile) {
                        if (!$uploadedFile) {
                            continue;
                        }
                        $tender->newDocument(ModulesEnum::Procurement, $uploadedFile, [PermissionEnum::TenderRead->value], Auth::user());
                    }
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

    try {
        DB::beginTransaction();

        $tender = Tender::findOrFail($request->tender_id);
        $user = Auth::user();

        // Check if user can approve this tender
        if (!$this->workflow->canApproveModel($tender, $user)) {
            return redirect()->route('initiatetender.index')->with('error', 'You are not authorized to approve this tender.');
        }

        // Use workflow to approve
        $approved = $this->workflow->approve(
            $tender,
            $user,
            TenderApprovalStatusEnum::APPROVED,
            $request->reason,
            'ApprovalStatus' // Use the correct status column name
        );

        if (!$approved) {
            throw new Exception('Workflow approval failed');
        }

        // Update tender status to published after approval
        $tender->update([
            'Status' => TenderStatusEnum::Published,
            'ModifiedBy' => $user->id,
            'ModifiedOn' => now(),
        ]);

        // Handle restricted tender invitations
        $invitationsSent = 0;
        if ($tender->isRestricted()) {
            $invitationsSent = $this->sendRestrictedTenderInvitations($tender);
        }

        activity()
            ->performedOn($tender)
            ->causedBy($user)
            ->withProperties([
                'action' => 'approve',
                'approval_remarks' => $request->reason,
                'previous_status' => 'Pending',
                'new_status' => 'Approved',
                'tender_type' => $tender->TenderType->value,
                'invitations_sent' => $invitationsSent
            ])
            ->log('Tender approved and published with ID: ' . $tender->Id);

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

    // AJAX: return distinct top-level item categories for the selected tender category
  public function allowedCategories(Request $request)
{
    try {
        $tenderCategoryId = $request->input('tender_category_id');
        
        if (!$tenderCategoryId) {
            return response()->json([
                'ok' => false,
                'message' => 'Tender category ID is required',
                'categories' => []
            ], 400);
        }

        Log::info("Fetching allowed categories for tender category: {$tenderCategoryId}");

        // Query the junction table to get allowed item types for this tender category
        $allowedItemTypes = DB::table('t_TenderCategoryItemTypes')
            ->where('TenderCategoryId', $tenderCategoryId)
            ->where('IsActive', 1)
            ->pluck('ItemTypeId')
            ->toArray();

        Log::info("Found item type IDs", ['item_types' => $allowedItemTypes]);

        if (empty($allowedItemTypes)) {
            Log::warning("No active item types found for tender category: {$tenderCategoryId}");
            return response()->json([
                'ok' => true,
                'message' => 'No item categories configured for this tender category',
                'categories' => []
            ]);
        }

        // Fetch the actual item categories - CORRECTED TABLE NAME
        $categories = DB::table('t_ItemCategories')
            ->whereIn('Id', $allowedItemTypes)
            ->whereNull('DeletedBy')
            ->select('Id', 'Name')
            ->orderBy('Name')
            ->get();

        Log::info("Retrieved categories", [
            'count' => $categories->count(),
            'categories' => $categories->toArray()
        ]);

        return response()->json([
            'ok' => true,
            'categories' => $categories,
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
 public function rejectTender(Request $request)
{
    $this->authorize(PermissionEnum::TenderUpdate, Tender::class);

    $request->validate([
        'tender_id' => 'required|exists:t_Tenders,Id',
        'reason' => 'required|string|max:1000',
    ]);

    try {
        DB::beginTransaction();

        $tender = Tender::findOrFail($request->tender_id);
        $user = Auth::user();

        // Use workflow to reject
        $rejected = $this->workflow->reject(
            $tender,
            $user,
            TenderApprovalStatusEnum::REJECTED,
            $request->reason,
            'ApprovalStatus' // Use the correct status column name
        );

        if (!$rejected) {
            throw new Exception('Workflow rejection failed');
        }

        // Keep tender as draft when rejected
        $tender->update([
            'Status' => TenderStatusEnum::Draft,
            'ModifiedBy' => $user->id,
            'ModifiedOn' => now(),
        ]);

        activity()
            ->performedOn($tender)
            ->causedBy($user)
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
        // Active rounds (Status 'O' for Open). If none, fall back to all active suppliers.
        $activeRounds = \App\Models\Procurement\Prequalification\PrequalificationRound::where('Status', 'O')
            ->where('StartDate', '<=', now())
            ->where('EndDate', '>=', now())
            ->pluck('RoundID');

        // Base supplier query: active suppliers, proper supplier type, with needed relations
        $supplierQuery = \App\Models\ThirdParies\Supplier::query()
            ->where('Active_Status', 1)
            ->whereHas('thirdParty.types', function ($q) {
                $q->where('Code', 'like', 'SU-%');
            })
            ->with(['thirdParty', 'supplierCategory.itemCategories']);

        if ($activeRounds->isNotEmpty()) {
            // Prefer suppliers in active rounds; include rows with NULL RoundID just in case
            $supplierQuery->where(function ($q) use ($activeRounds) {
                $q->whereIn('RoundID', $activeRounds)->orWhereNull('RoundID');
            });
        }

        $prequalifiedSuppliers = $supplierQuery->get();

        Log::info('Suppliers fetch — activeRounds=' . $activeRounds->count() . ', suppliers=' . $prequalifiedSuppliers->count());

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
            // Add any categories from pivot t_ThirdParty_SupplierCategory (resilient across DB schemas)
            try {
                $pivotCats = \App\Support\SupplierCategoryResolver::getCategoryIdsForThirdParty((int)$supplier->ThirdPartyID);
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

            $suppliers->push([
                'Id' => $supplier->Id,
                'SupplierName' => $thirdParty->ThirdPartyName,
                'ThirdPartyName' => $thirdParty->ThirdPartyName,
                'Email' => $thirdParty->Email ?? '', // Include Email for restricted tender invitations
                'Phone' => $thirdParty->Phone ?? '',
                'CategoryId' => null, // No longer used - categories come from SupplierCategory mapping
                'SupplierCategoryID' => $supplierCategoryIds->first(), // Prefer first mapped category if any
                'ItemCategoryIds' => array_values(array_unique(array_map('intval', $itemCategoryIds))), // All categories supplier can serve (incl. top-level)
                'RoundID' => $supplier->RoundID,
                'ApplicationStatus' => 'Prequalified', // Since they're in t_Suppliers, they're prequalified
                'ThirdPartyID' => $supplier->ThirdPartyID,
            ]);
        }

        // Remove duplicates based on supplier ID (a supplier might have multiple records)
        $result = $suppliers->unique('Id')->values();
        Log::info('Suppliers prepared for UI: ' . $result->count());
        try {
            $sample = $result->take(3)->map(function ($s) {
                return [
                    'Id' => $s['Id'] ?? null,
                    'Name' => $s['ThirdPartyName'] ?? $s['SupplierName'] ?? null,
                    'ItemCategoryIds' => array_slice($s['ItemCategoryIds'] ?? [], 0, 12),
                ];
            });
            Log::info('Suppliers sample (first 3)', ['sample' => $sample]);
        } catch (\Throwable $e) {
            // guard
        }
        return $result;
    }

    // Return allowed ItemType IDs for a tender category (FK Id)
    private function allowedItemTypeIdsForTender(int $tenderCategoryId): array
    {
        if (!$tenderCategoryId) return [];
        $ids = \App\Models\Procurement\TenderCategory::with('itemTypes')
            ->where('Id', $tenderCategoryId)
            ->first()?->itemTypes->pluck('Id')->all() ?? [];

        if (empty($ids)) {
            $label = \App\Models\Procurement\TenderCategory::where('Id', $tenderCategoryId)->value('TenderCategory');
            $types = DB::table('t_ItemTypes')
                ->join('t_CodeDetails', 't_ItemTypes.TypeName', '=', 't_CodeDetails.Id')
                ->pluck('t_ItemTypes.Id', 't_CodeDetails.Description');
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
    private function isItemAllowedForTender(int $itemId, int $tenderTopCategoryId, array $allowedTypeIds): bool
    {
        $item = ItemMasterList::select('Id', 'Category', 'ItemType')->find($itemId);
        if (!$item) return false;

        // Top-level category match
        $itemTop = $this->resolveTopLevelCategoryId((int)$item->Category);
        if ($tenderTopCategoryId && $itemTop && $itemTop !== (int)$tenderTopCategoryId) {
            return false;
        }
        // Item type allowed
        if (!empty($allowedTypeIds) && !in_array((int)$item->ItemType, $allowedTypeIds, true)) {
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
    $this->authorize(PermissionEnum::TenderRead, Tender::class);
    
    $tender = Tender::findOrFail($id);
    
    try {
        $history = $this->workflow->historyForModel($tender);
        
        return view('procurement.tendering.tendersetup.tenderinitiation.workflow-history', compact(
            'tender',
            'history'
        ));
    } catch (Exception $e) {
        Log::error('Failed to fetch workflow history: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to load workflow history.');
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
            'Status' => 'dr',
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
            Log::info('Attempting to attach document', [
                'tender_id' => $tender->Id,
                'filename' => $uploadedFile->getClientOriginalName(),
                'size' => $uploadedFile->getSize(),
                'mime' => $uploadedFile->getMimeType()
            ]);

            $test = $tender->newDocument(
                ModulesEnum::Procurement,
                $uploadedFile,
                [PermissionEnum::TenderWrite->value],
                Auth::user()
            );

            $uploadedCount++;
            Log::info('Document attached successfully', [
                'tender_id' => $tender->Id,
                'filename' => $uploadedFile->getClientOriginalName(),
                'full_log' => $test
            ]);

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

    Log::info('Document attachment completed', [
        'tender_id' => $tender->Id,
        'uploaded' => $uploadedCount,
        'failed' => $failedCount
    ]);
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
