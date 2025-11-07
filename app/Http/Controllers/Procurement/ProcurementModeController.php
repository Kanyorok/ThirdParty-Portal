<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\ProcurementMode;

class ProcurementModeController extends Controller
{
    public function index()
    {
        $modes = ProcurementMode::orderBy('CreatedOn', 'desc')->get();
        return view('procurement.procurement_modes.index', compact('modes'));
    }

    public function create()
    {
        return view('procurement.procurement_modes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:t_ProcurementModes,Name',
            'description' => 'nullable|string',
        ]);

        // Generate UniqueCode
        $prefix = 'PM'; // Prefix for Procurement Modes
        $lastMode = ProcurementMode::where('UniqueCode', 'like', "$prefix%")
            ->orderBy('Id', 'desc')
            ->first();

        $lastCode = $lastMode ? intval(substr($lastMode->UniqueCode, strlen($prefix))) : 0;
        $nextCode = str_pad($lastCode + 1, 3, '0', STR_PAD_LEFT);
        $uniqueCode = $prefix . $nextCode;

        ProcurementMode::create([
            'Name' => $request->name,
            'Description' => $request->description,
            'UniqueCode' => $uniqueCode,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
            'CreatedOn' => now(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('procurement-modes.index')->with('success', 'Procurement mode created successfully.');
    }

    public function show(ProcurementMode $procurement_mode)
    {
        return view('procurement.procurement_modes.show', [
            'procurement_mode' => $procurement_mode->load('timelines'),
        ]);
    }

    public function edit(ProcurementMode $procurement_mode)
    {
        return view('procurement.procurement_modes.edit', compact('procurement_mode'));
    }

    public function update(Request $request, ProcurementMode $procurement_mode)
    {
        $request->validate([
            'name' => 'required|unique:t_ProcurementModes,Name,' . $procurement_mode->Id . ',Id',
            'description' => 'nullable|string',
        ]);

        $procurement_mode->update([
            'Name' => $request->name,
            'Description' => $request->description,
            'ModifiedBy' => auth()->user()->Id,
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('procurement-modes.index')->with('success', 'Procurement mode updated successfully.');
    }

    public function destroy(ProcurementMode $procurement_mode)
    {
        $procurement_mode->delete();

        return redirect()->route('procurement-modes.index')->with('success', 'Procurement mode deleted successfully.');
    }
}
