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
            'name' => 'required|string|max:255',
            'type' => 'required|in:good,service',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:t_item_categories,id',
            'unit_of_measure' => 'nullable|string|max:100',
            'unit_price' => 'nullable|numeric|min:0',
            'service_scope' => 'nullable|string',
        ]);

        $validated['CreatedBy'] = auth()->user()->Id;
        $validated['ModifiedBy'] = auth()->user()->Id; 

        Item::create($validated);

        return redirect()->route('procurement.items.index')->with('success', 'Item created successfully.');
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
            'name' => 'required|string|max:255',
            'type' => 'required|in:good,service',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:t_Item_categories,id',
            'unit_of_measure' => 'nullable|string|max:100',
            'unit_price' => 'nullable|numeric|min:0',
            'service_scope' => 'nullable|string',
        ]);

        $item->update($validated);

        return redirect()->route('procurement.items.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return redirect()->route('procurement.items.index')->with('success', 'Item deleted successfully.');
    }
}
