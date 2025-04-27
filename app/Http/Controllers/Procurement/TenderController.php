<?php

namespace App\Http\Controllers\Procurement;

use App\Models\Procurement\Tender;
use App\Models\Procurement\ProcurementMode;
use App\Http\Controllers\Controller;
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
        $currencies = config('app.currencies'); // Assuming you have a config file for currencies

        return view('procurement.tenders.create', compact('procurementModes', 'currencies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'ProcurementModeId' => 'required|exists:t_ProcurementModes,id',
            'EstimatedValue' => 'required|numeric|min:0',
            'Currency' => 'required|string|max:3',
            'StartDate' => 'required|date|after_or_equal:today',
        ]);

        $tender = new Tender();
        $tender->TenderNumber = 'TNDR-' . Str::upper(Str::random(8)); // Generate a unique tender number
        $tender->Title = $request->Title;
        $tender->Description = $request->Description;
        $tender->ProcurementModeId = $request->ProcurementModeId;
        $tender->EstimatedValue = $request->EstimatedValue;
        $tender->Currency = $request->Currency;
        $tender->StartDate = $request->StartDate;
        $tender->Status = 'open'; // Default status
        $tender->CreatedBy = Auth::id(); // Assuming you have authentication set up
        $tender->ModifiedBy = Auth::id();
        $tender->save();

        // Auto-generate stage deadlines
        $timelineStages = \App\Models\Procurement\ModeTimeline::where('ProcurementModeId', $request->ProcurementModeId)->get();
        $startDate = Carbon::parse($request->StartDate);

        foreach ($timelineStages as $stage) {
            $endDate = (clone $startDate)->addDays($stage->DurationDays - 1);

            \App\Models\Procurement\TenderStage::create([
                'TenderId' => $tender->Id,
                'Stage' => $stage->Stage,
                'DurationDays' => $stage->DurationDays,
                'StartDate' => $startDate,
                'EndDate' => $endDate,
            ]);

            // Prepare the next stage to start after the current one ends
            $startDate = $endDate->copy()->addDay();
        }

        return redirect()->route('tendering-process.index')->with('success', 'Tender created successfully.');
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

        return view('procurement.tenders.edit', compact('tender', 'procurementModes', 'currencies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $request->validate([
            'Title' => 'required|string|max:255',
            'Description' => 'required|string|max:1000',
            'ProcurementModeId' => 'required|exists:t_ProcurementModes,Id',
            'EstimatedValue' => 'required|numeric|min:0',
            'Currency' => 'required|string|max:50',
            'StartDate' => 'required|date',
            'Status' => 'required|in:open,closed,cancelled,awarded',
        ]);

        // Find the tender by ID
        $tender = Tender::findOrFail($id);

        // Update the tender with the validated data
        $tender->update([
            'Title' => $request->Title,
            'Description' => $request->Description,
            'ProcurementModeId' => $request->ProcurementModeId,
            'EstimatedValue' => $request->EstimatedValue,
            'Currency' => $request->Currency,
            'StartDate' => $request->StartDate,
            'Status' => $request->Status,
        ]);

        // Redirect back with a success message
        return redirect()->route('tendering-process.index')->with('success', 'Tender updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tender = Tender::findOrFail($id); 
        $tender->delete(); // Delete the tender

        return redirect()->route('tendering-process.index')->with('success', 'Tender deleted successfully.');
    }
}
