<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\Store;
use App\Models\Core\Branch;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;



class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::all(); 
        return view('inventory.stores.index', compact('stores'));
    }

    public function create()
    {
        $branches = Branch::all();
        return view('inventory.stores.create', compact('branches'));
    }

public function store(Request $request)
{
    $validatedData = $request->validate([
        'StoreName'   => 'required|string|max:255',
        'BranchID'    => 'required|integer',
        'Status'      => 'required|boolean',
    ]);

    try {
        $store = new Store($validatedData);
        $store->CreatedBy = Auth::id();
        $store->CreatedOn = now();
        $store->ModifiedBy = Auth::id();
        $store->ModifiedOn = now();
        $store->save();

        // Generate StoreID after save (using the auto-increment Id)
        $store->StoreID = 'STR-' . str_pad($store->Id, 5, '0', STR_PAD_LEFT);
        $store->save();

        return redirect()->route('stores.index')->with('success', 'Store created successfully.');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Failed to create store: ' . $e->getMessage());
    }
}

    public function show($Id)
    {
        $store = Store::findOrFail($Id);
        return view('inventory.stores.show', compact('store'));
    }
    public function edit($Id)
    {
        $store = Store::findOrFail($Id);
        $branches = Branch::all();
        return view('inventory.stores.edit', compact('store', 'branches'));
        
    }
    public function update(Request $request, $Id)
    {
        $validatedData = $request->validate([
            'StoreName'   => 'required|string|max:255',
            'BranchID'    => 'required|integer',
            'Status'      => 'required|boolean',
        ]);

        try {
            $store = Store::findOrFail($Id);
            $store->update($validatedData);
            return redirect()->route('stores.index')->with('success', 'Store updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update store: ' . $e->getMessage());
        }
    }
    public function destroy($Id)
    {
        try {
            $store = Store::findOrFail($Id);
            $store->delete();
            return redirect()->route('stores.index')->with('success', 'Store deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete store: ' . $e->getMessage());
        }
    }


    }


