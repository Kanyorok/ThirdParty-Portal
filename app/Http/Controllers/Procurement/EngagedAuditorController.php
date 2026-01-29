<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\EngagedAuditor;
use App\Models\Procurement\SasraAuditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EngagedAuditorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $engagedAuditors = EngagedAuditor::with('auditor')->get();

        return view('procurement.engaged-auditor.index', compact('engagedAuditors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $auditors = SasraAuditor::where('Status', 'Active')->get();

        return view('procurement.engaged-auditor.create', compact('auditors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'AuditorId' => 'required|exists:t_Auditors,Id',
            'EngagementStartDate' => 'required|date',
            'EngagementEndDate' => 'nullable|date|after_or_equal:EngagementStartDate',
            'EngagementStatus' => 'nullable|string|max:55',
        ]);

        $validated['EngagementStatus'] = $validated['EngagementStatus'] ?? 'Active';
        $validated['CreatedBy'] = Auth::id();
        $validated['ModifiedBy'] = Auth::id();

        EngagedAuditor::create($validated);

        return redirect()->route('engaged-auditors.index')->with('success', 'Engaged auditor created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $engagedAuditor = EngagedAuditor::findOrFail($id);
        $auditors = SasraAuditor::where('Status', 'Active')->get();

        return view('procurement.engaged-auditor.edit', compact('engagedAuditor', 'auditors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'AuditorId' => 'required|exists:t_Auditors,Id',
            'EngagementStartDate' => 'required|date',
            'EngagementEndDate' => 'nullable|date|after_or_equal:EngagementStartDate',
            'EngagementStatus' => 'nullable|string|max:55',
        ]);

        $validated['ModifiedBy'] = Auth::id();

        // Set EngagementStatus to 'Inactive' if EngagementEndDate is present
        if (! empty($validated['EngagementEndDate'])) {
            $validated['EngagementStatus'] = 'Inactive';
        }

        $engagedAuditor = EngagedAuditor::findOrFail($id);
        $engagedAuditor->update($validated);

        return redirect()->route('engaged-auditors.index')->with('success', 'Engaged auditor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $engagedAuditor = EngagedAuditor::findOrFail($id);
        $engagedAuditor->delete();

        return redirect()->route('engaged-auditors.index')->with('success', 'Engaged auditor deleted successfully.');
    }
}
