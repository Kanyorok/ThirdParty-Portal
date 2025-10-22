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
                ->addColumn('Category', function ($item) {
                    return optional($item->category)->Name ?? 'Uncategorized';
                })
                ->addColumn('ParentCategory', function ($item) {
                    return optional(optional($item->category)->parent)->Name ?? '—';
                })
                ->addColumn('ItemType', function ($item) {
                    return optional($item->itemType)->TypeName ?? '—';
                })
                ->addColumn('InventoryType', function ($item) {
                    return optional($item->inventoryType)->Type ?? '—';
                })
                ->addColumn('UOM', function ($item) {
                    return optional($item->uom)->Code ?? '—';
                })
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
                ->addColumn('ItemPrice', function ($item) {
                    return optional($item->price)->ActualPrice ?? '—';
                })
                ->addColumn('Action', function ($item) {
                    $viewUrl = route('itemmasterlist.show', $item->Id);
                    $editUrl = route('itemmasterlist.edit', $item->Id);
                    $deleteUrl = route('itemmasterlist.destroy', $item->Id);

                    $actions = '
        <a href="' . $viewUrl . '" class="btn btn-sm btn-primary">View</a>
        <a href="' . $editUrl . '" class="btn btn-sm btn-warning">Edit</a>
    ';

                    if ($item->inUse()) {
                        $actions .= '<span class="badge bg-info">In Use</span>';
                    } else {
                        $actions .= '
            <button type="button" class="btn btn-danger btn-sm"
                onclick="if(confirm(\'⚠️ Are you sure you want to delete this unit?\')) {
                    this.disabled=true;
                    this.innerText=\'Submitting...\';
                    document.getElementById(\'delete-form-' . $item->Id . '\').submit();
                }">
                Delete
            </button>
            <form id="delete-form-' . $item->Id . '"
                  action="' . $deleteUrl . '"
                  method="POST" style="display:none;">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>
        ';
                    }

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

        if ($item->inUse()) {
            return redirect()->route('itemmaster.index')
                ->with('error', '❌ Cannot delete this item because it is currently in use.');
        }

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
