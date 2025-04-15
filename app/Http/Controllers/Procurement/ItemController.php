<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Item;
use App\Models\Procurement\ItemCategory;
use Illuminate\Http\Request;
use App\Services\Procurement\ItemExportService;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Item::with('category');

        // Apply filters if present
        if ($request->filled('category_id')) {
            $query->where('CategoryId', $request->category_id);
        }

        if ($request->filled('type')) {
            $query->where('Type', $request->type);
        }

        $items = $query->get();
        $categories = ItemCategory::all(); // for filter dropdown

        return view('procurement.items.index', compact('items', 'categories'));
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

    public function download(Request $request, ItemExportService $exportService)
    {
        return $exportService->download(
            $request->only(['category_id', 'type']),
            $request->get('format', 'xlsx')
        );
    }
}
