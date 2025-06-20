<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Enums\TenderApprovalStatusEnum;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\ItemMasterList;
use App\Models\Procurement\Tender;
use App\Models\Procurement\ProcurementMode;
use App\Models\Core\Currency;
use App\Enums\TenderTypeEnum;
use App\Http\Controllers\Controller;
use App\Enums\TenderCategoryEnum;
use App\Enums\TenderStatusEnum;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\PlanLineItems;
use App\Models\Procurement\ProcurementPlan;
use App\Models\Procurement\TenderCategory;
use App\Models\Procurement\TenderItems;
use App\Models\Procurement\TenderSupplier;
use App\Models\ThirdParies\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ProcurementPlanStatusEnum;

class TenderController extends Controller
{
    public function index()
    {
        //Check if the user has permission to view tenders using the enum set
        $this->authorize(PermissionEnum::TenderRead, Tender::class);

        $tenders = Tender::with(['procurementMode', 'currency'])->get();
        //Log activity for viewing tenders
        activity()
            ->performedOn(new Tender())
            ->causedBy(Auth::user())
            ->withProperties(['action' => 'viewed'])
            ->log('Viewed tenders list');
        return view('procurement.tendering.tendersetup.tenderinitiation.index', compact('tenders'));
    }

    public function create()
    {
        //Check if the user has permission to create tenders using the enum set
        $this->authorize(PermissionEnum::TenderWrite, Tender::class);
        $procurementModes = ProcurementMode::all();
        $currencies = Currency::all();
        $tenderTypes = TenderTypeEnum::cases();
        $statuses = TenderStatusEnum::cases();
        $suppliers = collect();
        $tenderCategories = TenderCategory::select('Id', 'TenderCategory')->get();
        $AllItemsCategories = ItemCategories::select('Id', 'Name')->whereNull('ParentId')->get();
        $allItemsWithCategoryIds = ItemMasterList::select('Id', 'ItemName', 'Category')->get();
        $procurementPlan = ConsolidatedProcurementPlan::select('PlanID', 'ReferenceNumber', 'Title')->where('Status', ProcurementPlanStatusEnum::Approved)
            ->get();

        $allCurrency = Currency::select('Id', 'Name', 'Code', 'Symbol')->get();

        //return$allItemsWithCategoryIds = Item::select('Id', 'ItemName', 'Category')->get()->groupBy('Category');

        // Fetch procurement plans
        $procurementPlans = ConsolidatedProcurementPlan::select('PlanID', 'ReferenceNumber', 'Title')->where('Status', ProcurementPlanStatusEnum::Approved)
            ->get()
            ->keyBy('PlanID');

        // Fetch plan line items with related item details
        $itemsCategories = PlanLineItems::select('LineItemID', 'PlanID', 'ItemID', 'MergedQty')
            ->with(['item' => function ($query) {
                $query->select('Id', 'ItemName');
            }])
            ->get();

        // Initialize the output arrays
        $procurementPlansOutput = [];
        $planItemData = [];

        // Group line items by PlanID and build output
        foreach ($itemsCategories as $lineItem) {
            $planId = $lineItem->PlanID;
            $item = $lineItem->item;

            // Skip if item is null
            if (!$item) {
                continue;
            }

            // Add to procurementPlansOutput
            $procurementPlansOutput[$planId][] = [
                'id' => $planId,
                'itemId' => $item->Id,
                'name' => $item->ItemName,
                'plannedQty' => $lineItem->MergedQty,
            ];

            // Add to planItemData
            $planItemData[$planId][] = [
                'itemId' => $item->Id,
                'name' => $item->ItemName,
                'plannedQty' => $lineItem->MergedQty,
            ];
        }

        //Getting List of All Suppliers
        $suppliers = Supplier::select('Id', 'SupplierName', 'CategoryId')->get();

        // Log activity for creating tender
        activity()
            ->performedOn(new Tender())
            ->causedBy(Auth::user())
            ->withProperties(['action' => 'create'])
            ->log('View tender creation');
        //return $procurementPlansOutput;
        //return $planItemData;
        return view('procurement.tendering.tendersetup.tenderinitiation.create', compact(
            'procurementModes',
            'currencies',
            'tenderTypes',
            'tenderCategories',
            'statuses',
            'itemsCategories',
            'procurementPlan',
            'AllItemsCategories',
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
        //return $request->all();
        //Check if the user has permission to create tenders using the enum set
        $this->authorize(PermissionEnum::TenderWrite, Tender::class);
        //return $request->all();
        //  $validated = $request->validate([
        //     'title' => 'required|string|max:255',
        //     'tender_type' => 'required|string',
        //     'tender_category_id' => 'required|integer',
        //     'item_category_id' => 'required|integer', //Commented out since the planItem aint working
        //     'procurement_plan_id' => 'required|integer',

        //     'plan_items' => 'required|array',
        //     'plan_items.*.item_id' => 'required|integer',
        //     'plan_items.*.qty' => 'required|integer',
        //     'plan_items.*.pr_ref' => 'nullable|string|max:255',
        //     'plan_items.*.file' => 'nullable|file|max:5120', // max 5MB

        //     'manual_items' => 'nullable|array',
        //     'manual_items.*.item_id' => 'required|integer',
        //     'manual_items.*.qty' => 'required|integer',
        //     'manual_items.*.pr_ref' => 'nullable|string|max:255',
        //     'manual_items.*.specs_file' => 'nullable|file|max:5120',

        //     'scope_of_work' => 'required|string',
        //     'instructions' => 'required|string',
        //     'submission_deadline' => 'required|date|after_or_equal:today',
        //     'opening_date' => 'required|date|after_or_equal:submission_deadline',

        //     'suppliers' => 'nullable|array',
        //     'suppliers.*' => 'integer',

        //     'documents' => 'nullable|array',
        //     'documents.*' => 'file|max:5120', // If documents are files, otherwise adjust

        // ]);

        //return $request->tender_type.' Tender';

        DB::beginTransaction();

        try {
            // Create Tender Record
            $tender = new Tender();
            //$tender_type = $request->tender_type === 'Open' ? 'op' : 'rs';

            $tender->TenderNo = 'TNDR-' . Str::upper(Str::random(8));
            $tender->Title = $request->title;
            $tender->TenderType = TenderTypeEnum::from($request->tender_type);
            $tender->TenderCategory = $request->tender_category_id; // Consider mapping ID to string if needed
            $tender->ScopeOfWork = $request->scope_of_work;
            $tender->Instructions = $request->instructions;
            $tender->SubmissionDeadline = $request->submission_deadline;
            $tender->OpeningDate = $request->opening_date;
            $tender->Status = 'dr';
            //$tender->ProcurementModeId = $request->procurement_plan_id;
            $tender->EstimatedValue = null; // Optional value
            $tender->ItemCategoryId = $request->item_category_id; // Consider mapping to name if needed
            $tender->CreatedBy = Auth::id();
            $tender->CreatedOn = now();
            $tender->ModifiedBy = Auth::id();
            $tender->ModifiedOn = now();
            $tender->RelatedPRID = '1'; // rm error fo nulable after later migration
            $tender->CurrencyId = $request->currency_id; // rm error fo nulable after later migration
            $tender->save();

            $tenderId = $tender->Id;
            // Insert Plan Items
            if (!empty($request->plan_items)) {
                foreach ($request->plan_items as $compositeKey => $item) {
                    $split = explode('-', $compositeKey);
                    $planItemId = (int)end($split);
                    TenderItems::create([
                        'TenderID' => $tenderId,
                        'SourceType' => 'PLAN',
                        'ItemID' => $item['item_id'] ?? null, // Assuming item_id is optional
                        'PlanItemID' => $planItemId,
                        'PlannedQty' => $item['qty'],
                        'QtyToTender' => $item['qty'],
                        'ItemCategory' => $request->item_category_id, // Consider mapping to name
                        'Remarks' => null,
                        'RelatedPRID' => $item['pr_ref'] ?? null,
                        'CreatedBy' => auth()->user()->Id,
                        'ModifiedBy' => auth()->user()->Id,
                    ]);
                }
            }

            // Insert Manual Items
            if (!empty($request->manual_items)) {
                foreach ($request->manual_items as $manualItem) {
                    //Rm if no item_id is provided
                    if (empty($manualItem['item_id'])) {
                        //continue; // Skip if item_id is not provided
                    } else {
                        TenderItems::create([
                            'TenderID' => $tenderId,
                            'SourceType' => 'MANUAL',
                            'ItemID' => $manualItem['item_id'] ?? null, // Assuming item_id is optional
                            'ManualItemDescription' => null, // Optional: Provide actual description if needed
                            'PlannedQty' => null,
                            'QtyToTender' => $manualItem['qty'],
                            'ItemCategory' => $request->item_category_id,
                            'Remarks' => null,
                            'RelatedPRID' => $manualItem['pr_ref'] ?? null,
                            'CreatedBy' => auth()->user()->Id,
                            'ModifiedBy' => auth()->user()->Id,
                        ]);
                    }
                }
            }

            // Insert suppliers
            if (!empty($request->suppliers)) {
                foreach ($request->suppliers as $supplierId) {
                    TenderSupplier::create([
                        'TenderID' => $tenderId,
                        'SupplierID' => $supplierId,
                        'CreatedBy' => auth()->user()->Id,
                        'ModifiedBy' => auth()->user()->Id,
                    ]);
                }
            }
            // Insert documents if provided
            // TODO: Loop through uploaded documents and store them
            // foreach ($request->documents as $file) {
            // $path = $file->store('tender_documents');
            // $tender->documents()->create([
            //     'FilePath' => $path,
            //     'FileName' => $file->getClientOriginalName(),
            // ]);
            // }

            DB::commit();

            // Log activity for tender creation
            activity()
                ->performedOn($tender)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Tender created successfully with ID: ' . $tenderId);
            return redirect()->route('initiatetender.index')->with('success', 'Tender created successfully.');
            // return response()->json([
            //     'success' => true,
            //     'message' => 'Tender created successfully.',
            //     'tender_id' => $tenderId
            // ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
            Log::error("--- CREATE TENDER ERROR --- " . $e->getMessage());
            Log::error($e);
            //return $e->getMessage();
            return redirect()->route('initiatetender.index')->with('error', 'Failed to create Tender. Please try again.');
        }
        // Auto-generate stage deadlines
        //$this->generateTenderStages($tender, $request->ProcurementModeId, $request->StartDate);
    }

    public function show(string $id)
    {
        //Check if the user has permission to view tenders using the enum set
        $this->authorize(PermissionEnum::TenderRead, Tender::class);
        $tender = Tender::findOrFail($id);
        //Check if the tender has passed approval process
        $show = false;
        if ($tender->ApprovalStatus === TenderApprovalStatusEnum::REJECTED || $tender->ApprovalStatus === TenderApprovalStatusEnum::APPROVED) {
            $show = true;
        }
        $items = TenderItems::where('TenderID', $id)->get();
        $suppliers = TenderSupplier::where('TenderID', $id)->with('supplier')->get();
        //Extract item names from TenderItems using relationship
        $tenderCategory = TenderCategory::find($tender->tender_category_id);
        $itemCategory = ItemCategories::find($tender->item_category_id);
        $currency = Currency::find($tender->currency_id);
        $procurementPlan = ProcurementPlan::find($tender->procurement_plan_id);

        $planItems = [];//PlanItem::where('tender_id', $id)->get();
        $manualItems = [];// ManualItem::where('tender_id', $id)->get();

        // Attach item names manually to planItems and manualItems
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
            'show' // Pass the show variable to the view
        ));
    }


    public function edit(string $id)
    {
        //Check if the user has permission to view tenders using the enum set
        $this->authorize(PermissionEnum::TenderUpdate, Tender::class);
        $tender = Tender::findOrFail($id);
        //Check if the tender has passed approval process
        $show = false;
        if ($tender->ApprovalStatus === TenderApprovalStatusEnum::REJECTED || $tender->ApprovalStatus === TenderApprovalStatusEnum::APPROVED) {
            $show = true;
        }
        $items = TenderItems::where('TenderID', $id)->where('ItemCategory', $tender->ItemCategoryId)->get();
        $otherItemsForThatTender = ItemMasterList::where('Category', $tender->ItemCategoryId)->get();
        // foreach ($items as $key => $value) {
        //      $r[]=$value->item->ItemName;
        // }
        // return $r;
        $suppliers = TenderSupplier::where('TenderID', $id)->with('supplier')->get();
        $existingSupplierIds = $suppliers->pluck('SupplierID')->toArray();
        $otherSuppliers = Supplier::select('Id', 'SupplierName', 'CategoryId', 'ContactPhone', 'ContactEmail')
            ->where('CategoryId', $tender->ItemCategoryId)
            ->whereNotIn('Id', $existingSupplierIds)
            ->get();
        //Extract item names from TenderItems using relationship
        $tenderCategory = TenderCategory::find($tender->TenderCategory)->Id;
        $tenderCategories = TenderCategory::select('Id', 'TenderCategory')->get();
        $itemCategory = ItemCategories::find($tender->ItemCategoryId)->Name;
        $itemCategoryID = ItemCategories::find($tender->ItemCategoryId)->Id;
        $currency = $tender->CurrencyId;

        $allCurrency = Currency::select('Id', 'Name', 'Code', 'Symbol')->get();
        $procurementPlan = ProcurementPlan::find($tender->procurement_plan_id);

        $planItems = [];//PlanItem::where('tender_id', $id)->get();
        $manualItems = [];// ManualItem::where('tender_id', $id)->get();

        // Attach item names manually to planItems and manualItems
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
            'show', // Pass the show variable to the view
            'allCurrency'
        ));
    }

    public function update(Request $request, $id)
    {
        //return $request->all();
        //Check if the user has permission to update tenders using the enum set
        $this->authorize(PermissionEnum::TenderUpdate, Tender::class);
        //Check the update is coming from which form
        $type = $request->input('type');

        if ($type === 'editTenderInfo') {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'tender_category_id' => 'required|exists:t_TenderCategories,Id',
                //'item_category_id' => 'required|exists:item_categories,Id',
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
                //$tender->ItemCategoryId = $validated['item_category_id'];
                $tender->CurrencyId = $validated['currency_id'];
                $tender->SubmissionDeadline = $validated['submission_deadline'];
                $tender->OpeningDate = $validated['opening_date'];

                // Optional: handle file uploads here if needed

                $tender->save();

                DB::commit();

                // Log activity
                activity()
                    ->performedOn($tender)
                    ->causedBy(Auth::user())
                    ->withProperties(['action' => 'update'])
                    ->log('Tender updated successfully with ID: ' . $id);

                return redirect()->route('initiatetender.edit', $id)->with('success', 'Tender Info updated successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('--- UPDATE TENDER ERROR --- ' . $e->getMessage());
                Log::error($e);
                return $e->getMessage();
                // Log the error and return an error message

                return redirect()->route('initiatetender.edit', $id)->with('error', 'Failed to update Tender. Please try again.');
            }
        } elseif ($type == 'crudItem') {
            //CrudType
            $crudType = $request->crudType;
            //Check if the crud is create
            if ($crudType == 'addItem') {
                // Validate the request for item CRUD operations
                $validated = $request->validate([
                    'item_id' => 'required|integer',
                    'QtyToTender' => 'required|integer|min:1',
                    'pr_ref' => 'nullable|string|max:255',
                    //'file' => 'nullable|file|max:5120', // max 5MB
                ]);
                DB::beginTransaction();
                try {
                    // Create a new TenderItems record
                    $tenderItem = new TenderItems();
                    $tenderItem->TenderID = $id;
                    $tenderItem->SourceType = 'MANUAL'; // Assuming manual entry
                    $tenderItem->ItemID = $validated['item_id'];
                    $tenderItem->itemCategory = $request->itemCategoryID; // Assuming this is passed from the form
                    $tenderItem->QtyToTender = $validated['QtyToTender'];
                    $tenderItem->RelatedPRID = $validated['pr_ref'] ?? null;
                    $tenderItem->CreatedBy = Auth::id();
                    $tenderItem->ModifiedBy = Auth::id();
                    $tenderItem->save();

                    DB::commit();

                    // Log activity for item creation
                    activity()
                        ->performedOn($tenderItem)
                        ->causedBy(Auth::user())
                        ->withProperties(['action' => 'create'])
                        ->log('Tender Item created successfully with ID: ' . $tenderItem->Id);

                    return redirect()->route('initiatetender.edit', $id)->with('success', 'Tender Item created successfully.');
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("--- CREATE TENDER ITEM ERROR --- " . $e->getMessage());
                    Log::error($e);
                    return redirect()->route('initiatetender.edit', $id)->with('error', 'Failed to create Tender Item. Please try again.');
                }

            } elseif ($crudType == 'deleteItem') {
                DB::beginTransaction();
                try {
                    // Find and delete the TenderItems record
                    $tenderItem = TenderItems::findOrFail($request->item_id);
                    $tenderItem->delete();

                    DB::commit();

                    // Log activity for item deletion
                    activity()
                        ->performedOn($tenderItem)
                        ->causedBy(Auth::user())
                        ->withProperties(['action' => 'delete'])
                        ->log('Tender Item deleted successfully with ID: ' . $tenderItem->Id);

                    return redirect()->route('initiatetender.edit', $id)->with('success', 'Tender Item deleted successfully.');
                } catch (\Exception $e) {
                    DB::rollBack();
                    return $e->getMessage();
                    Log::error("--- DELETE TENDER ITEM ERROR --- " . $e->getMessage());
                    Log::error($e);
                    return redirect()->route('initiatetender.edit', $id)->with('error', 'Failed to delete Tender Item. Please try again.');
                }
            }
        } elseif ($type == 'crudSupplier') {
            //CrudType
            $crudType = $request->crudType;
            //Check if the crud is create
            if ($crudType == 'addSupplier') {
                return $request->all();
                // Validate the request for supplier CRUD operations
                $validated = $request->validate([
                    'supplier_id' => 'required|integer|exists:t_Suppliers,Id',
                ]);
                DB::beginTransaction();
                try {
                    // Create a new TenderSupplier record
                    $tenderSupplier = new TenderSupplier();
                    $tenderSupplier->TenderID = $id;
                    $tenderSupplier->SupplierID = $validated['supplier_id'];
                    $tenderSupplier->CreatedBy = Auth::id();
                    $tenderSupplier->ModifiedBy = Auth::id();
                    $tenderSupplier->save();

                    DB::commit();

                    // Log activity for supplier creation
                    activity()
                        ->performedOn($tenderSupplier)
                        ->causedBy(Auth::user())
                        ->withProperties(['action' => 'create'])
                        ->log('Tender Supplier created successfully with ID: ' . $tenderSupplier->Id);

                    return redirect()->route('initiatetender.edit', $id)->with('success', 'Tender Supplier created successfully.');
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("--- CREATE TENDER SUPPLIER ERROR --- " . $e->getMessage());
                    Log::error($e);
                    return redirect()->route('initiatetender.edit', $id)->with('error', 'Failed to create Tender Supplier. Please try again.');
                }

            } elseif ($crudType == 'deleteSupplier') {
                return $request->all();
                DB::beginTransaction();
                try {
                    // Find and delete the TenderSupplier record
                    $tenderSupplier = TenderSupplier::findOrFail($request->supplier_id);
                    $tenderSupplier->delete();

                    DB::commit();

                    // Log activity for supplier deletion
                    activity()
                        ->performedOn($tenderSupplier)
                        ->causedBy(Auth::user())
                        ->withProperties(['action' => 'delete'])
                        ->log('Tender Supplier deleted successfully with ID: ' . $tenderSupplier->Id);

                    return redirect()->route('initiatetender.edit', $id)->with('success', 'Tender Supplier deleted successfully.');
                } catch (\Exception $e) {
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

            // Handle restricted tender suppliers
            if ($request->TenderType === TenderTypeEnum::Restricted->value) {
                $tender->suppliers()->sync($request->suppliers ?? []);
            } else {
                $tender->suppliers()->detach();
            }
            DB::commit();
            // Log activity for tender update
            activity()
                ->performedOn($tender)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Tender updated successfully with ID: ' . $id);
            return redirect()->route('initiatetender.index')->with('success', 'Tender updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("--- UPDATE TENDER ERROR --- " . $e->getMessage());
            Log::error($e);
            return redirect()->route('initiatetender.index')->with('error', 'Failed to update Tender. Please try again.');
        }
    }

    public function destroy(string $id)
    {
        //Check if the user has permission to delete tenders using the enum set
        $this->authorize(PermissionEnum::TenderDelete, Tender::class);
        try {
            $tender = Tender::findOrFail($id);
            $tender->delete();
            // Log activity for tender deletion
            activity()
                ->performedOn($tender)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Tender deleted successfully with ID: ' . $id);
            return redirect()->route('initiatetender.index')->with('success', 'Tender deleted successfully.');
        } catch (\Throwable $th) {
            Log::error("--- DELETE TENDER ERROR --- " . $th->getMessage());
            Log::error($th);
            return redirect()->route('initiatetender.index')->with('error', 'Failed to delete Tender. Please try again.');
        }
    }

    /**
     * Generate tender stages based on procurement mode timeline
     */
    protected function generateTenderStages(Tender $tender, $procurementModeId, $startDate)
    {
        $timelineStages = \App\Models\Procurement\ModeTimeline::where('ProcurementModeId', $procurementModeId)->get();
        $startDate = Carbon::parse($startDate);

        foreach ($timelineStages as $stage) {
            $endDate = (clone $startDate)->addDays($stage->DurationDays - 1);

            \App\Models\Procurement\TenderStage::create([
                'TenderId' => $tender->Id,
                'Stage' => $stage->Stage,
                'DurationDays' => $stage->DurationDays,
                'StartDate' => $startDate,
                'EndDate' => $endDate,
            ]);

            $startDate = $endDate->copy()->addDay();
        }
    }


    //Approval and rejection of tender
    public function approveTender(Request $request)
    {
        //Check if the user has permission to approve tenders using the enum set
        $this->authorize(PermissionEnum::TenderApproval, Tender::class);
        ///validate the request
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);
        try {
            $tender = Tender::findOrFail($request->tender_id);
            //Publish the tender and send to suppliers or public
            $tender->Status = TenderStatusEnum::Published;
            //Put approval status based on the enum \App\Enums\TenderApprovalStatusEnum::APPROVED
            $tender->ApprovalStatus = TenderApprovalStatusEnum::APPROVED;
            $tender->ApprovalRemarks = $request->reason;
            $tender->ModifiedBy = Auth::id();
            $tender->save();
            // Log activity for tender approval
            activity()
                ->performedOn($tender)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'approve'])
                ->log('Tender approved successfully with ID: ' . $tender->Id);

            return redirect()->route('initiatetender.index')->with('success', 'Tender approved successfully.');
        } catch (\Throwable $th) {
            Log::error("--- APPROVE TENDER ERROR --- " . $th->getMessage());
            Log::error($th);
            return redirect()->route('initiatetender.index')->with('error', 'Failed to approve Tender. Please try again.');
        }
    }

    public function rejectTender(Request $request)
    {
        //Check if the user has permission to reject tenders using the enum set
        $this->authorize(PermissionEnum::TenderApproval, Tender::class);
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);
        try {
            $tender = Tender::findOrFail($request->tender_id);
            //Reject the tender and send back to draft
            $tender->Status = TenderStatusEnum::Closed;
            //Put approval status based on the enum \App\Enums\TenderApprovalStatusEnum::REJECTED
            $tender->ApprovalStatus = TenderApprovalStatusEnum::REJECTED;
            $tender->ApprovalRemarks = $request->reason;
            $tender->ModifiedBy = Auth::id();
            $tender->save();
            // Log activity for tender rejection
            activity()
                ->performedOn($tender)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'reject'])
                ->log('Tender rejected successfully with ID: ' . $tender->Id);

            return redirect()->route('initiatetender.index')->with('success', 'Tender rejected successfully.');
        } catch (\Throwable $th) {
            Log::error("--- REJECT TENDER ERROR --- " . $th->getMessage());
            Log::error($th);
            return redirect()->route('initiatetender.index')->with('error', 'Failed to reject Tender. Please try again.');
        }
    }

}
