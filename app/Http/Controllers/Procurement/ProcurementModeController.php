<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\ProcurementMode;

class ProcurementModeController extends Controller
{
    public function index()
    {
        $modes = ProcurementMode::latest()->get();
        return view('procurement.procurement_modes.index', compact('modes'));
    }

    public function create()
    {
        return view('procurement.procurement_modes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:t_ProcurementModes,name',
            'description' => 'nullable|string',
        ]);

        // Generate UniqueCode
        $prefix = 'PM'; // Prefix for Procurement Modes
        $lastMode = ProcurementMode::where('UniqueCode', 'like', "$prefix%")->orderBy('id', 'desc')->first();

        // Determine the next sequential number
        $lastCode = $lastMode ? intval(substr($lastMode->UniqueCode, strlen($prefix))) : 0;
        $nextCode = str_pad($lastCode + 1, 3, '0', STR_PAD_LEFT);

        // Assign the generated UniqueCode
        $uniqueCode = $prefix . $nextCode;

        ProcurementMode::create([
            'Name' => $request->name,
            'Description' => $request->description,
            'UniqueCode' => $uniqueCode,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);

        return redirect()->route('procurement-modes.index')->with('success', 'Procurement mode created successfully.');
    }

    public function show(ProcurementMode $procurement_mode)
    {
        return view('procurement.procurement_modes.show', compact('procurement_mode'));
    }

    public function edit(ProcurementMode $procurement_mode)
    {
        return view('procurement.procurement_modes.edit', compact('procurement_mode'));
    }

    public function update(Request $request, ProcurementMode $procurement_mode)
    {
        $request->validate([
            'name' => 'required|unique:t_ProcurementModes,name,' . $procurement_mode->id,
            'description' => 'nullable|string',
        ]);

        $procurement_mode->update([
            'Name' => $request->name,
            'Description' => $request->description,
            'ModifiedBy' => auth()->user()->Id,
        ]);

        return redirect()->route('procurement-modes.index')->with('success', 'Procurement mode updated successfully.');
    }

    public function destroy(ProcurementMode $procurement_mode)
    {
        $procurement_mode->delete();

        return redirect()->route('procurement-modes.index')->with('success', 'Procurement mode deleted successfully.');
    }
}
