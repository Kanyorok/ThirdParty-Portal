<?php

namespace App\Http\Controllers\Procurement;

use App\Models\Procurement\Tender;
use App\Models\Procurement\ProcurementMode;
use App\Models\Core\Currency;
use App\Enums\TenderTypeEnum;
use App\Http\Controllers\Controller;
use App\Enums\TenderCategoryEnum;
use App\Enums\TenderStatusEnum;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\Item;
use App\Models\Procurement\ItemCategory;
use App\Models\Procurement\PlanLineItems;
use App\Models\Procurement\ProcurementPlan;
use App\Models\Procurement\TenderCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Enum;

class TenderController extends Controller
{
    public function index()
    {
        $tenders = Tender::with(['procurementMode', 'currency'])->get();
        return view('procurement.tendering.tendersetup.tenderinitiation.index', compact('tenders'));
    }

    public function create()
    {
        $procurementModes = ProcurementMode::all();
        $currencies = Currency::all();
        $tenderTypes = TenderTypeEnum::cases();
        $statuses = TenderStatusEnum::cases();
        $suppliers = collect();
        $tenderCategories = TenderCategory::select('Id', 'TenderCategory')->get();
        $AllItemsCategories = ItemCategory::select('Id', 'Name')->whereNull('ParentId')->get();
        // $itemsCategories = PlanLineItems::select('LineItemId','PlanID','ItemId')->get();
        // // Fetching item with respective planitem data
        // foreach($itemsCategories as $item){
        //     return$lineItems=Item::all()->where('Id',$item->ItemId);
        //     $planItemData[]=[
        //         $item->PlanID:{

        //         }
        //     ]
        // }
        $procurementPlan= ConsolidatedProcurementPlan::select('PlanID','ReferenceNumber','Title')
            //->where('Status', 'Approved') //Add this once approval process is done
            ->get();

        // Fetch procurement plans
        $procurementPlans = ConsolidatedProcurementPlan::select('PlanID', 'ReferenceNumber', 'Title')
            // ->where('Status', 'Approved') // Uncomment when approval process is ready
            ->get()
            ->keyBy('PlanID'); // Key by PlanID for easier lookup

        // Fetch plan line items with related item details
        $itemsCategories = PlanLineItems::select('LineItemID', 'PlanID', 'ItemID', 'MergedQty')
            ->with(['item' => function ($query) {
                $query->select('Id', 'ItemName'); // Make sure column name matches the DB
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
                'name' => $item->ItemName,
                'plannedQty' => $lineItem->MergedQty,
            ];

            // Add to planItemData
            $planItemData[$planId] = [
                'name' => $item->ItemName,
                'plannedQty' => $lineItem->MergedQty,
            ];
        }
        //return $procurementPlansOutput;
        return view('procurement.tendering.tendersetup.tenderinitiation.create', compact(
            'procurementModes',
            'currencies',
            'tenderTypes',
            'tenderCategories',
            'statuses',
            'suppliers',
            'itemsCategories',
            'procurementPlan',
            'AllItemsCategories',
            'procurementPlansOutput',
            'procurementPlans',
            'planItemData'
            
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'TenderType' => ['required', new Enum(TenderTypeEnum::class)],
            'TenderCategory' => ['required', new Enum(TenderCategoryEnum::class)],
            'ScopeOfWork' => 'nullable|string',
            'Instructions' => 'nullable|string',
            'SubmissionDeadline' => 'required|date|after:today',
            'OpeningDate' => 'required|date|after:SubmissionDeadline',
            'Status' => ['required', new Enum(TenderStatusEnum::class)],
            'RelatedPRID' => 'nullable|integer',
            'ProcurementModeId' => 'required|integer|exists:t_ProcurementModes,id',
            'Currency' => 'required|exists:t_Currencies,Id',
            'EstimatedValue' => 'nullable|numeric|min:0',
            'StartDate' => 'required|date|after_or_equal:today',
        ]);

        $tender = new Tender();
        $tender->TenderNo = 'TNDR-' . Str::upper(Str::random(8));
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
        $fillData['Currency'] = $request->Currency;
        $tender->fill($fillData);
        $tender->TenderType = TenderTypeEnum::from($request->TenderType);
        $tender->TenderCategory = TenderCategoryEnum::from($request->TenderCategory);
        $tender->Status = TenderStatusEnum::from($request->Status);
        $tender->CreatedBy = Auth::id();
        $tender->save();

        // Handle restricted tender suppliers
        if ($request->TenderType === TenderTypeEnum::Restricted->value && $request->has('suppliers')) {
            $tender->suppliers()->attach($request->suppliers);
        }

        // Handle document uploads
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('tender_documents');
                $tender->documents()->create([
                    'FilePath' => $path,
                    'FileName' => $file->getClientOriginalName(),
                ]);
            }
        }

        // Auto-generate stage deadlines
        $this->generateTenderStages($tender, $request->ProcurementModeId, $request->StartDate);

        return redirect()->route('initiatetender.index')->with('success', 'Tender created successfully.');
    }

    public function show(string $id)
    {
        $tender = Tender::with([
            'stages',
            'procurementMode',
            'currency',
            'suppliers',
            'documents'
        ])->findOrFail($id);

        return view('procurement.tendering.tendersetup.tenderinitiation.show', compact('tender'));
    }

    public function edit(string $id)
    {
        $tender = Tender::with(['suppliers'])->findOrFail($id);
        $procurementModes = ProcurementMode::all();
        $currencies = Currency::all();
        $tenderTypes = TenderTypeEnum::cases();
        $tenderCategories = TenderCategoryEnum::cases();
        $statuses = TenderStatusEnum::cases();

        return view('procurement.tendering.tendersetup.tenderinitiation.edit', compact(
            'tender',
            'procurementModes',
            'currencies',
            'tenderTypes',
            'tenderCategories',
            'statuses'
        ));
    }

    public function update(Request $request, $id)
    {
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

        return redirect()->route('initiatetender.index')->with('success', 'Tender updated successfully.');
    }

    public function destroy(string $id)
    {
        $tender = Tender::findOrFail($id);
        $tender->delete();
        return redirect()->route('initiatetender.index')->with('success', 'Tender deleted successfully.');
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
}
