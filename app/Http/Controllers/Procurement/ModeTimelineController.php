<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\ModeTimeline;
use App\Models\Procurement\ProcurementMode;

class ModeTimelineController extends Controller
{
    /**
     * Store a newly created ModeTimeline.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ProcurementModeId' => 'required|exists:t_ProcurementModes,Id',
            'Stage' => 'required|string|max:255',
            'DurationDays' => 'required|integer|min:1',
        ]);

        ModeTimeline::create($request->all());

        return redirect()->back()->with('success', 'Timeline added successfully.');
    }

    /**
     * Show the form for editing the specified timeline (optional).
     */
    public function edit($id)
    {
        $timeline = ModeTimeline::findOrFail($id);
        $procurement_mode = $timeline->procurementMode;
        return view('procurement.timelines.edit', compact('timeline', 'procurement_mode'));
    }

    /**
     * Update the specified timeline.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'ProcurementModeId' => 'required|exists:t_ProcurementModes,Id',
            'Stage' => 'required|string|max:255',
            'DurationDays' => 'required|integer|min:1',
        ]);

        $timeline = ModeTimeline::findOrFail($id);
        $timeline->update([
            'ProcurementModeId' => $request->ProcurementModeId,
            'Stage' => $request->Stage,
            'DurationDays' => $request->DurationDays,
        ]);

        return redirect()->route('procurement-modes.show', $request->ProcurementModeId)
        ->with('success', 'Timeline updated successfully.');
    }

    /**
     * Remove the specified timeline from storage.
     */
    public function destroy($id)
    {
        $timeline = ModeTimeline::findOrFail($id);
        $timeline->delete();

        return redirect()->back()->with('success', 'Timeline deleted successfully.');
    }
}
