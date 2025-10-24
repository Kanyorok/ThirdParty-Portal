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
use App\Imports\ItemMasterListImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ItemMasterListExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;

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
            $items = ItemMasterList::with([
                'category.parent',
                'itemType',
                'inventoryType',
                'uom',
                'price',
                'status'
            ]);

            return DataTables::of($items)
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
            $viewUrl   = route('itemmasterlist.show', $item->Id);
            $editUrl   = route('itemmasterlist.edit', $item->Id);
            $deleteUrl = route('itemmasterlist.destroy', $item->Id);

            $actions = '
                <div class="btn-group" role="group">
                    <a href="' . $viewUrl . '" class="btn btn-sm btn-view" data-bs-toggle="tooltip" title="View Item">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="' . $editUrl . '" class="btn btn-sm btn-edit" data-bs-toggle="tooltip" title="Edit Item">
                        <i class="bi bi-pencil-square"></i>
                    </a>
            ';

            if ($item->inUse()) {
                $actions .= '
                    <span class="btn btn-sm btn-info disabled" data-bs-toggle="tooltip" title="Item is in use and cannot be deleted">
                        <i class="bi bi-info-circle"></i>
                    </span>
                ';
            } else {
                $actions .= '
                    <button type="button" class="btn btn-sm btn-delete delete-btn" 
                        data-bs-toggle="tooltip" title="Delete Item"
                        data-item-id="' . $item->Id . '"
                        data-item-name="' . e($item->ItemName) . '">
                        <i class="bi bi-trash"></i>
                    </button>
                ';
            }

            $actions .= '</div>';

            return $actions;
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

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new ItemMasterListImport, $request->file('file'));
            return back()->with('success', 'Items imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function export()
    {
        return Excel::download(new ItemMasterListExport, 'ItemMasterList.xlsx');
    }

    public function store(ItemMasterListRequest $request)
    {
        $this->authorize('create', ItemMasterList::class);

        $validated = $request->validated();
        $document = $request->file('Document');  // ✅ handled same as FleetDriver
        $image = $request->file('ImageUpload');

        // Get default Active status
        $validated['Status'] = CodeDetail::where('CodeID', 'ItemStatus')
            ->where('Description', 'Active')
            ->value('Id');

        $this->service->create($validated, $image, $document);

        return redirect()->route('itemmaster.index')
            ->with('success', 'Item created successfully.');
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
        $this->authorize('update', $item);

        $validated = $request->validated();
        $document = $request->file('Document');
        $image = $request->file('ImageUpload');

        // Handle image removal if requested
        if ($request->has('remove_image') && $request->input('remove_image') == '1') {
            if ($item->ImageId) {
                \App\Models\DMS\Image::destroy($item->ImageId);
                $item->ImageId = null;
            }
        }

        $this->service->update($Id, $validated, $image, $document);

        return redirect()->route('itemmaster.index')
            ->with('success', 'Item updated successfully.');
    }


    public function destroy($Id)
    {
        $item = ItemMasterList::findOrFail($Id);
        $this->authorize('delete', $item);

        if ($item->inUse()) {
            return redirect()->route('itemmaster.index')
                ->with('error', '❌ Cannot delete this item because it is currently in use.');
        }

        $this->service->delete($item);

        return redirect()->route('itemmaster.index')
            ->with('success', 'Item deleted successfully.');
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
