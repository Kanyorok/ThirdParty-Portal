<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Procurement\ProcurementPeriod;


class ProcurementPeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $periods = ProcurementPeriod::orderBy('StartDate', 'desc')->get();
        return view('procurement.periods.index', compact('periods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('procurement.periods.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Title' => 'nullable|string|max:255',
            'StartDate' => 'required|date',
            'EndDate' => 'required|date|after_or_equal:StartDate',
        ]);

        $validated['CreatedBy'] = Auth::id();
        $validated['ModifiedBy'] = Auth::id();
        // Generate UniqueCode
        $prefix = 'PP'; // Prefix for Procurement Periods
        $lastPeriod = ProcurementPeriod::where('ProcurementPeriodNumber', 'like', "$prefix%")->orderBy('id', 'desc')->first();
        // Determine the next sequential number
        $lastCode = $lastPeriod ? intval(substr($lastPeriod->ProcurementPeriodNumber, strlen($prefix))) : 0;
        $nextCode = str_pad($lastCode + 1, 3, '0', STR_PAD_LEFT);
        // Assign the generated UniqueCode
        $validated['ProcurementPeriodNumber'] = $prefix . $nextCode;

        ProcurementPeriod::create($validated);
        return redirect()->route('procurement-periods.index')->with('success', 'Procurement period created.');
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
        $period = ProcurementPeriod::findOrFail($id);
        return view('procurement.periods.edit', compact('period'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'Title' => 'nullable|string|max:255',
            'StartDate' => 'required|date',
            'EndDate' => 'required|date|after_or_equal:StartDate',
        ]);

        $validated['ModifiedBy'] = Auth::id();

        $period = ProcurementPeriod::findOrFail($id);
        $period->update($validated);

        return redirect()->route('procurement-periods.index')->with('success', 'Procurement period updated.');  
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $period = ProcurementPeriod::findOrFail($id);
        $period->delete();

        return redirect()->route('procurement-periods.index')->with('success', 'Procurement period deleted.');
    }
}
