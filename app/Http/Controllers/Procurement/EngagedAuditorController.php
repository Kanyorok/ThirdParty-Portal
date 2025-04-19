<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\EngagedAuditor;
use Illuminate\Support\Facades\Auth;
use App\Models\Procurement\SasraAuditor;

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
            'SasraAuditorId' => 'required|exists:t_SasraAuditors,Id',
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
