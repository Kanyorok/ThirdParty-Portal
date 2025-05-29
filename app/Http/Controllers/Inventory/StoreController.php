<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreRequest;
use App\Services\Inventory\StoreService;
use App\Models\Inventory\Store;
use App\Models\Core\Branch;

class StoreController extends Controller
{
    protected $service;

    public function __construct(StoreService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $stores = Store::all();
        return view('inventory.stores.index', compact('stores'));
    }

    public function create()
    {
        $this->authorize('create', Store::class);
        $branches = Branch::all();
        return view('inventory.stores.create', compact('branches'));
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

    public function edit($Id)
    {
        $store = Store::findOrFail($Id);
        $this->authorize('update', $store);
        $branches = Branch::all();
        return view('inventory.stores.edit', compact('store', 'branches'));
    }

    public function update(StoreRequest $request, $Id)
    {
        $store = Store::findOrFail($Id);
        $this->authorize('update', $store);

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

        try {
            $this->service->delete($store);
            return redirect()->route('stores.index')->with('success', 'Store deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete store: ' . $e->getMessage());
        }
    }
}
