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

class TenderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenders = Tender::with('procurementMode')->get();
        return view('procurement.tenders.index', compact('tenders'));
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

        return view('procurement.tenders.create', compact(
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
            'TenderType' => ['required', 'in:'.implode(',', TenderTypeEnum::values())],
            'TenderCategory' => 'required|in:Goods,Services,Works',
            'ScopeOfWork' => 'nullable|string',
            'Instructions' => 'nullable|string',
            'SubmissionDeadline' => 'required|date|after:today',
            'OpeningDate' => 'required|date|after:SubmissionDeadline',
            'Status' => 'required|in:Draft,Published,Closed',
            'RelatedPRID' => 'nullable|integer',
            'ProcurementModeId' => 'required|exists:t_ProcurementModes,id',
            'EstimatedValue' => 'required|numeric|min:0',
            'Currency' => 'required|string|max:3',
            'StartDate' => 'required|date|after_or_equal:today',
        ]);

        $tender = new Tender();
        $tender->TenderNo = 'TNDR-' . Str::upper(Str::random(8));
        $tender->Title = $request->Title;
        $tender->TenderType = TenderTypeEnum::from($request->TenderType);
        $tender->TenderCategory = $request->TenderCategory;
        $tender->ScopeOfWork = $request->ScopeOfWork;
        $tender->Instructions = $request->Instructions;
        $tender->SubmissionDeadline = $request->SubmissionDeadline;
        $tender->OpeningDate = $request->OpeningDate;
        $tender->Status = $request->Status;
        $tender->RelatedPRID = $request->RelatedPRID;
        $tender->ProcurementModeId = $request->ProcurementModeId;
        $tender->EstimatedValue = $request->EstimatedValue;
        $tender->Currency = $request->Currency;
        $tender->StartDate = $request->StartDate;
        $tender->CreatedBy = Auth::id();
        $tender->save();

        // Auto-generate stage deadlines if needed
        $timelineStages = \App\Models\Procurement\ModeTimeline::where('ProcurementModeId', $request->ProcurementModeId)->get();
        $startDate = Carbon::parse($request->StartDate);

        foreach ($timelineStages as $stage) {
            $endDate = (clone $startDate)->addDays($stage->DurationDays - 1);

            \App\Models\Procurement\TenderStage::create([
                'TenderId' => $tender->TenderID,
                'Stage' => $stage->Stage,
                'DurationDays' => $stage->DurationDays,
                'StartDate' => $startDate,
                'EndDate' => $endDate,
            ]);

            $startDate = $endDate->copy()->addDay();
        }

        return redirect()->route('tenders.index')->with('success', 'Tender created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tender = Tender::with(['stages', 'procurementMode'])->findOrFail($id);
        return view('procurement.tenders.show', compact('tender'));
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
        $tenderCategories = ['Goods', 'Services', 'Works'];
        $statuses = ['Draft', 'Published', 'Closed'];

        return view('procurement.tenders.edit', compact(
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
            'TenderType' => ['required', 'in:'.implode(',', TenderTypeEnum::values())],
            'TenderCategory' => 'required|in:Goods,Services,Works',
            'ScopeOfWork' => 'nullable|string',
            'Instructions' => 'nullable|string',
            'SubmissionDeadline' => 'required|date',
            'OpeningDate' => 'required|date|after:SubmissionDeadline',
            'Status' => 'required|in:Draft,Published,Closed',
            'RelatedPRID' => 'nullable|integer',
            'ProcurementModeId' => 'required|exists:t_ProcurementModes,id',
            'EstimatedValue' => 'required|numeric|min:0',
            'Currency' => 'required|string|max:3',
            'StartDate' => 'required|date',
        ]);

        $tender = Tender::findOrFail($id);
        $tender->update([
            'Title' => $request->Title,
            'TenderType' => TenderTypeEnum::from($request->TenderType),
            'TenderCategory' => $request->TenderCategory,
            'ScopeOfWork' => $request->ScopeOfWork,
            'Instructions' => $request->Instructions,
            'SubmissionDeadline' => $request->SubmissionDeadline,
            'OpeningDate' => $request->OpeningDate,
            'Status' => $request->Status,
            'RelatedPRID' => $request->RelatedPRID,
            'ProcurementModeId' => $request->ProcurementModeId,
            'EstimatedValue' => $request->EstimatedValue,
            'Currency' => $request->Currency,
            'StartDate' => $request->StartDate,
            'ModifiedBy' => Auth::id(),
        ]);

        return redirect()->route('tenders.index')->with('success', 'Tender updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tender = Tender::findOrFail($id); 
        $tender->delete();

        return redirect()->route('tenders.index')->with('success', 'Tender deleted successfully.');
    }
}