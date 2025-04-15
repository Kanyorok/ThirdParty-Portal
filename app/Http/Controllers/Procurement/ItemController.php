<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Item;
use App\Models\Procurement\ItemCategory;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $items = Item::with('category')->get();
        return view('procurement.items.index', compact('items'));
    }

    public function create()
    {
        $categories = ItemCategory::all();
        return view('procurement.items.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
                                         'Name'            => 'required|string|max:255',
                                         'Type'            => 'required|in:good,service',
                                         'Description'     => 'nullable|string',
                                         'CategoryId'     => 'nullable|exists:t_ItemCategories,id',
                                         'UOM' => 'nullable|string|max:100',
                                         'UnitPrice'      => 'nullable|numeric|min:0',
                                         'ServiceScope'   => 'nullable|string',
                                        ]);

        $validated['CreatedBy'] = auth()->user()->Id;
        $validated['ModifiedBy'] = auth()->user()->Id;

        Item::create($validated);

        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }


    public function show(Item $item)
    {
        return view('procurement.items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $categories = ItemCategory::all();
        return view('procurement.items.edit', compact('item', 'categories'));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
                                         'Name'            => 'required|string|max:255',
                                         'Type'            => 'required|in:good,service',
                                         'Description'     => 'nullable|string',
                                         'CategoryId'     => 'nullable|exists:t_ItemCategories,id',
                                         'UOM' => 'nullable|string|max:100',
                                         'UnitPrice'      => 'nullable|numeric|min:0',
                                         'ServiceScope'   => 'nullable|string',
                                        ]);

        $item->update($validated);

        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
    }
}
