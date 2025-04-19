<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Item;
use App\Models\Procurement\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Procurement\ItemExportService;

class ItemController extends Controller
{
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
        $currencies = config('app.currencies'); 
        return view('procurement.items.create', compact('categories', 'currencies'));
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
                                         'Currency'       => 'nullable|string|max:3',
                                        ]);

         // Generate UniqueCode
        $type = $validated['Type'];
        $prefix = $type === 'good' ? 'IT' : 'SE';

        // Get the last item of the same type
        $lastItem = Item::where('Type', $type)->orderBy('id', 'desc')->first();

        // Determine the next sequential number
        $lastCode = $lastItem ? intval(substr($lastItem->UniqueCode, 2)) : 0;
        $nextCode = str_pad($lastCode + 1, 3, '0', STR_PAD_LEFT);

        // Assign the generated UniqueCode
        $validated['UniqueCode'] = $prefix . $nextCode;

        $validated['CreatedBy'] = Auth::id();
        $validated['ModifiedBy'] = Auth::id();

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
        $currencies = config('app.currencies'); 
        return view('procurement.items.edit', compact('item', 'categories', 'currencies'));
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
                                         'Currency'       => 'nullable|string|max:3',
                                        ]);

        // Check if the Type has changed
        if ($validated['Type'] !== $item->Type) {
            $type = $validated['Type'];
            $prefix = $type === 'good' ? 'IT' : 'SE';

            // Get the last item of the new type
            $lastItem = Item::where('Type', $type)->orderBy('id', 'desc')->first();

            // Determine the next sequential number
            $lastCode = $lastItem ? intval(substr($lastItem->UniqueCode, 2)) : 0;
            $nextCode = str_pad($lastCode + 1, 3, '0', STR_PAD_LEFT);

            // Assign the new UniqueCode
            $validated['UniqueCode'] = $prefix . $nextCode;
        }


        $validated['ModifiedBy'] = Auth::id();

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
