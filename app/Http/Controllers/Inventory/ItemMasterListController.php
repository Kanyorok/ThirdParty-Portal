<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\ItemMasterListRequest;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\InventoryType;
use App\Models\Inventory\ItemType;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\Inventory\PriceManagement;
use App\Models\Core\CodeDetail;
use App\Services\Inventory\ItemMasterListService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ItemMasterListController extends Controller
{
    protected ItemMasterListService $service;

    public function __construct(ItemMasterListService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(
                ItemMasterList::with('category.parent', 'itemType', 'inventoryType', 'uom', 'price', 'status')
            )
                ->addIndexColumn()
                ->addColumn('Category', fn($item) => optional($item->category)->Name ?? 'Uncategorized')
                ->addColumn('ParentCategory', fn($item) => optional(optional($item->category)->parent)->Name ?? '—')
                ->addColumn('ItemType', fn($item) => optional($item->itemType)->TypeName ?? '—')
                ->addColumn('InventoryType', fn($item) => optional($item->inventoryType)->Type ?? '—')
                ->addColumn('UOM', fn($item) => optional($item->uom)->Code ?? '—')
                ->addColumn('Status', function ($item) {
                    if ($item->status && $item->status->Description) {
                        $desc = $item->status->Description;
                        $badgeClass = match (strtolower($desc)) {
                            'active'   => 'bg-success',
                            'inactive' => 'bg-secondary',
                            default    => 'bg-warning',
                        };
                        return '<span class="badge ' . $badgeClass . '">' . e($desc) . '</span>';
                    }
                    return '<span class="badge bg-warning">Unknown</span>';
                })
                ->addColumn('ItemPrice', fn($item) => optional($item->price)->ActualPrice ?? '—')
                ->addColumn('Action', function ($item) {
                    return '
                        <a href="' . route('itemmasterlist.show', $item->Id) . '" class="btn btn-sm btn-primary">View</a>
                        <a href="' . route('itemmasterlist.edit', $item->Id) . '" class="btn btn-sm btn-warning">Edit</a>
                        <button onclick="confirmDelete(' . $item->Id . ')" class="btn btn-danger btn-sm">Delete</button>
                        <form id="delete-form-' . $item->Id . '" action="' . route('itemmasterlist.destroy', $item->Id) . '" method="POST" style="display:none;">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                        </form>';
                })
                ->rawColumns(['Status', 'Action'])
                ->make(true);
        }

        return view('inventory.itemmaster.itemmasterlist.index');
    }

    public function create()
    {
        $this->authorize('create', ItemMasterList::class);

        return view('inventory.itemmaster.itemmasterlist.create', [
            'categories' => ItemCategories::whereNull('ParentId')
                ->whereHas('status', fn($q) => $q->where('Description', 'Active'))
                ->get(),
            'status' => CodeDetail::where('CodeID', 'ItemStatus')->orderBy('Value')->get(),
            'itemTypes' => ItemType::all(),
            'uoms' => UnitOfMeasure::all(),
            'price' => PriceManagement::all(),
            'inventoryTypes' => InventoryType::all(),
        ]);
    }

    public function store(ItemMasterListRequest $request)
    {
        $this->authorize('create', ItemMasterList::class);

        $activeStatusId = CodeDetail::where('CodeID', 'ItemStatus')
            ->where('Description', 'Active')
            ->value('Id');

        $data = $request->validated();
        $data['Status'] = $activeStatusId;

        $this->service->create(
            $data,
            $request->file('ImageUpload'),
            $request->file('DocumentUpload')
        );

        return redirect()->route('itemmaster.index')->with('success', 'Item created successfully.');
    }

    public function show($Id)
    {
        $item = ItemMasterList::with('category.parent')->findOrFail($Id);
        $this->authorize('view', $item);

        return view('inventory.itemmaster.itemmasterlist.show', compact('item'));
    }

    public function edit($Id)
    {
        $item = ItemMasterList::findOrFail($Id);
        $this->authorize('update', $item);

        return view('inventory.itemmaster.itemmasterlist.edit', [
            'item' => $item,
            'categories' => ItemCategories::whereNull('ParentId')
                ->whereHas('status', fn($q) => $q->where('Description', 'Active'))
                ->get(),
            'status' => CodeDetail::where('CodeID', 'ItemStatus')->orderBy('Value')->get(),
            'subcategories' => ItemCategories::where('ParentId', $item->category?->ParentId ?? $item->Category)
                ->whereHas('status', fn($q) => $q->where('Description', 'Active'))
                ->get(),
            'itemTypes' => ItemType::all(),
            'uoms' => UnitOfMeasure::all(),
            'inventoryTypes' => InventoryType::all(),
            'priceManagement' => PriceManagement::all(),
        ]);
    }

    public function update(ItemMasterListRequest $request, $Id)
{
    $item = ItemMasterList::findOrFail($Id);
    $this->authorize('update', $item); // ✅ pass instance

    $this->service->update(
        $item,
        $request->validated(),
        $request->file('ImageUpload'),
        $request->file('DocumentUpload'),
        $request->boolean('remove_image')
    );

    return redirect()->route('itemmaster.index')->with('success', 'Item updated successfully.');
}


    public function destroy($Id)
    {
        $item = ItemMasterList::findOrFail($Id);
        $this->authorize('delete', $item);

        $this->service->delete($item);

        return redirect()->route('itemmaster.index')->with('success', 'Item deleted successfully.');
    }

    public function getSubcategories(Request $request)
    {
        return response()->json(
            ItemCategories::where('ParentId', $request->get('category_id'))
                ->whereHas('status', fn($q) => $q->where('Description', 'Active'))
                ->get(['Id', 'Name'])
        );
    }
}
