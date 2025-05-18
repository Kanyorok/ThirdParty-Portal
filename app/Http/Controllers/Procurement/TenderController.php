<?php

namespace App\Http\Controllers\Procurement;

use App\Models\Procurement\Tender;
use App\Models\Procurement\ProcurementMode;
use App\Enums\TenderTypeEnum;
use App\Http\Controllers\Controller;
use App\Enums\TenderCategoryEnum;
use App\Enums\TenderStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

use App\Models\Procurement\ModeTimeline;
use App\Models\Procurement\TenderStage;

class TenderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load procurementMode relationship
        $tenders = Tender::with('procurementMode')->latest()->paginate(10);
        return view('procurement.tendering.tendersetup.tenderinitiation.index', compact('tenders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $procurementModes = ProcurementMode::all();
        $currencies = config('app.currencies');
        $tenderTypes = TenderTypeEnum::cases();
        $tenderCategories = TenderCategoryEnum::cases();
        $statuses = TenderStatusEnum::cases();

        return view('procurement.tendering.tendersetup.tenderinitiation.create', compact(
            'procurementModes',
            'currencies',
            'tenderTypes',
            'tenderCategories',
            'statuses'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'TenderType' => ['required', Rule::in(TenderTypeEnum::values())],
            'TenderCategory' => ['required', Rule::in(TenderCategoryEnum::values())],
            'ScopeOfWork' => 'nullable|string',
            'Instructions' => 'nullable|string',
            'SubmissionDeadline' => 'required|date|after:today',
            'OpeningDate' => 'required|date|after:SubmissionDeadline',
            'Status' => ['required', Rule::in(TenderStatusEnum::values())],
            'RelatedPRID' => 'nullable|integer',
            'ProcurementModeId' => 'required|exists:t_ProcurementModes,id',
            'EstimatedValue' => 'required|numeric|min:0',
            'Currency' => 'required|string|max:3',
            'StartDate' => 'required|date|after_or_equal:today',
        ]);

        $tender = new Tender();
        $tender->TenderNo = 'TNDR-' . Str::upper(Str::random(8));
        $tender->Title = $request->Title; // or also  $validated['Title']
        $tender->TenderType = TenderTypeEnum::from($request->TenderType);

        $tender->TenderCategory = $request->TenderCategory;
        $tender->Status = $request->Status;

        $tender->ScopeOfWork = $request->ScopeOfWork;
        $tender->Instructions = $request->Instructions;
        $tender->SubmissionDeadline = $request->SubmissionDeadline;
        $tender->OpeningDate = $request->OpeningDate;
        $tender->RelatedPRID = $request->RelatedPRID;
        $tender->ProcurementModeId = $request->ProcurementModeId;
        $tender->EstimatedValue = $request->EstimatedValue;
        $tender->Currency = $request->Currency;
        $tender->StartDate = $request->StartDate;
        $tender->CreatedBy = Auth::id();
        $tender->save();

        $timelineStages = ModeTimeline::where('ProcurementModeId', $request->ProcurementModeId)->get();
        $currentStageStartDate = Carbon::parse($request->StartDate);

        foreach ($timelineStages as $stage) {
            $endDate = (clone $currentStageStartDate)->addDays($stage->DurationDays - 1);

            TenderStage::create([
                'TenderId' => $tender->Id,
                'Stage' => $stage->Stage,
                'DurationDays' => $stage->DurationDays,
                'StartDate' => $currentStageStartDate,
                'EndDate' => $endDate,
            ]);

            $currentStageStartDate = $endDate->copy()->addDay();
        }

        return redirect()->route('procurement.tendering.tendersetup.tenderinitiation.index')->with('success', 'Tender created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tender = Tender::with(['stages', 'procurementMode'])->findOrFail($id);
        return view('procurement.tendering.tendersetup.tenderinitiation.view', compact('tender'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $tender = Tender::findOrFail($id);
        $procurementModes = ProcurementMode::all();
        $currencies = config('app.currencies');
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'TenderType' => ['required', Rule::in(TenderTypeEnum::values())],
            'TenderCategory' => [' required ', Rule::enum(TenderCategoryEnum::class)],
            'ScopeOfWork' => 'nullable|string',
            'Instructions' => 'nullable|string',
            'SubmissionDeadline' => 'required|date',
            'OpeningDate' => 'required|date|after:SubmissionDeadline',
            'Status' => ['required', Rule::in(TenderStatusEnum::values())],
            'RelatedPRID' => 'nullable|integer',
            'ProcurementModeId' => 'required|exists:t_ProcurementModes,id',
            'EstimatedValue' => 'required|numeric|min:0',
            'Currency' => 'required|string|max:3',
            'StartDate' => 'required|date',
        ]);

        $tender = Tender::findOrFail($id);

        $updateData = $validated;

        $updateData['TenderType'] = TenderTypeEnum::from($validated['TenderType']);
        $updateData['TenderCategory'] = TenderCategoryEnum::from($validated['TenderCategory']);
        $updateData['Status'] = TenderStatusEnum::from($validated['Status']);

        $updateData['ModifiedBy'] = Auth::id();

        $tender->update($updateData);

        return redirect()->route('initiatetender.index')->with('success', 'Tender updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tender = Tender::findOrFail($id);
        $tender->delete();

        return redirect()->route('procurement.tendering.tendersetup.tenderinitiation.index')->with('success', 'Tender deleted successfully.');
    }
}
