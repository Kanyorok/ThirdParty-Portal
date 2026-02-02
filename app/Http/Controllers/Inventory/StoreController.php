<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreRequest;
use App\Models\Core\Branch;
use App\Models\Inventory\Store;
use App\Services\Inventory\StoreService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    protected $service;

    public function __construct(StoreService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $currentBranch = $request->user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id; 
        
        $stores = Store::where('BranchID', $branchId)
            ->withCount('stockItems')
            ->get();
            
        return view('inventory.stores.index', compact('stores'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Store::class);

        $currentBranch = $request->user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;
        $branch = Branch::find($branchId);

        $mainStoreExists = Store::where('BranchID', $branchId)
            ->where('IsMainStore', true)
            ->exists();

        return view('inventory.stores.create', compact('branch', 'mainStoreExists'));
    }

    public function store(StoreRequest $request)
    {
        $this->authorize('create', Store::class);

        try {
            $store = $this->service->create($request->validated());

            return redirect()->route('stores.index')->with('success', 'Store created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create store: ' . $e->getMessage())->withInput();
        }
    }

    public function show($Id)
    {
        $store = Store::findOrFail($Id);
        $this->authorize('view', $store);

        return view('inventory.stores.show', compact('store'));
    }

    public function edit($Id, Request $request)
    {
        $store = Store::findOrFail($Id);
        $this->authorize('update', $store);

        $currentBranch = $request->user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;
        $branch = Branch::find($branchId);

        $mainStoreExists = Store::where('BranchID', $branchId)
            ->where('IsMainStore', true)
            ->where('Id', '!=', $Id)
            ->exists();
        
        $hasStockItems = $store->stockItems()->exists();
        $stockItemsCount = $hasStockItems ? $store->stockItems()->count() : 0;

        return view('inventory.stores.edit', compact('store', 'branch', 'mainStoreExists', 'hasStockItems', 'stockItemsCount'));
    }

    public function update(StoreRequest $request, $Id)
    {
        $store = Store::findOrFail($Id);
        $this->authorize('update', $store);

        if ($store->Status == 1 && $request->Status == 0 && $store->hasStockItems()) {
            return redirect()->back()
                ->with('error', 'Cannot deactivate store with existing stock items. Please remove all stock items first.')
                ->withInput();
        }

        try {
            $this->service->update($store, $request->validated());

            return redirect()->route('stores.index')->with('success', 'Store updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update store: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($Id)
    {
        $store = Store::findOrFail($Id);
        $this->authorize('destroy', $store);

        if ($store->IsMainStore) {
            $otherStoresCount = Store::where('BranchID', $store->BranchID)
                ->where('Id', '!=', $Id)
                ->count();

            if ($otherStoresCount === 0) {
                return redirect()->back()->with('error', 'Cannot delete the main store as it is the only store for this branch.');
            }
        }
        
        if ($store->hasStockItems()) {
            return redirect()->back()->with('error', 'Cannot delete store with existing stock items. Please remove all stock items first.');
        }

        try {
            $this->service->delete($store);

            return redirect()->route('stores.index')->with('success', 'Store deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete store: ' . $e->getMessage());
        }
    }
}
