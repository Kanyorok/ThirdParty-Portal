<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\UnitOfMeasure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UOMController extends Controller
{
    public function index()
    {
        $units = UnitOfMeasure::all();
        return view('inventory.itemmaster.unitofmeasure.index', compact('units'));
    }
        public function create()
    {
        return view('inventory.itemmaster.unitofmeasure.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'Code' => 'required|string|max:50',
            'Name' => 'required|string|max:255',
            'BaseUnit' => 'nullable|boolean',
            'Active' => 'required|boolean',
        ]);

        $unit = UnitOfMeasure::create([
            'Code' => $request->Code,
            'Name' => $request->Name,
            'BaseUnit' => $request->BaseUnit,
            'Active' => $request->Active,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => Carbon::now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => Carbon::now(),
        ]);

        return redirect()->route('unitofmeasure.index')->with('success', 'Inventory type created successfully.');
    
    }

    public function show($Id)
    {
        $unit = UnitOfMeasure::findOrFail($Id);
        return response()->json($unit);
    }

    public function edit($Id)
    {
        $unit = UnitOfMeasure::findOrFail($Id);
        return response()->json($unit);
    }

    public function update(Request $request, $Id)
    {
        $request->validate([
            'Code' => 'required|string|max:50',
            'Name' => 'required|string|max:255',
            'BaseUnit' => 'nullable|boolean',
            'Active' => 'required|boolean',
        ]);

        $unit = UnitOfMeasure::findOrFail($Id);
        $unit->update([
            'Code' => $request->Code,
            'Name' => $request->Name,
            'BaseUnit' => $request->BaseUnit,
            'Active' => $request->Active,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => Carbon::now(),
        ]);

        return redirect()->route('unitofmeasure.index')->with('success', 'Unit of Measure Updated successfully.');
    }

    public function destroy($Id)
    {
        $unit = UnitOfMeasure::findOrFail($Id);
        $unit->DeletedBy = Auth::id();
        $unit->save();
        $unit->delete();

        return redirect()->route('unitofmeasure.index')->with('success', 'Unit of Measure Deleted successfully.');
    }
}