<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InventoryTypeController extends Controller
{
    public function index()
    {
        $types = InventoryType::all();
        return view('inventory.itemmaster.inventorytype.index', compact('types'));
    }

    public function create()
    {
        return view('inventory.itemmaster.inventorytype.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'Type' => 'required|string|max:255',
            'Status' => 'required|boolean',
        ]);

        InventoryType::create([
            'Type' => $request->Type,
            'Status' => $request->Status,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => Carbon::now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => Carbon::now(),
        ]);

        return redirect()->route('inventorytype.index')->with('success', 'Inventory type created successfully.');
    }

    public function edit($id)
    {
        $type = InventoryType::findOrFail($id);
        return view('inventory.itemmaster.inventorytype.edit', compact('type'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'Type' => 'required|string|max:255',
            'Status' => 'required|boolean',
        ]);

        $type = InventoryType::findOrFail($id);
        $type->update([
            'Type' => $request->Type,
            'Status' => $request->Status,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => Carbon::now(),
        ]);

        return redirect()->route('inventorytype.index')->with('success', 'Inventory type updated successfully.');
    }

    public function destroy($id)
    {
        $type = InventoryType::findOrFail($id);
        $type->DeletedBy = Auth::id();
        $type->save();
        $type->delete();

        return redirect()->route('inventorytype.index')->with('success', 'Inventory type deleted successfully.');
    }
}