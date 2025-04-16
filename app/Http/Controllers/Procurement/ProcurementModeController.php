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

        ProcurementMode::create($request->only('name', 'description'));

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

        $procurement_mode->update($request->only('name', 'description'));

        return redirect()->route('procurement-modes.index')->with('success', 'Procurement mode updated successfully.');
    }

    public function destroy(ProcurementMode $procurement_mode)
    {
        $procurement_mode->delete();

        return redirect()->route('procurement-modes.index')->with('success', 'Procurement mode deleted successfully.');
    }
}
