<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ItemMasterListExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\ItemMasterListRequest;
use App\Imports\ItemMasterListImport;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\InventoryType;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemType;
use App\Models\Inventory\PriceManagement;
use App\Models\Inventory\UnitOfMeasure;
use App\Services\Inventory\ItemMasterListService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ItemMasterListController extends Controller
{
    protected ItemMasterListService $service;

    public function __construct(ItemMasterListService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', ItemMasterList::class);

        $items = ItemMasterList::with([
            'category.parent',
            'itemType.type',
            'inventoryType.type',
            'uom',
            'price',
            'status',
        ])->get();

        return view('inventory.itemmaster.itemmasterlist.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', ItemMasterList::class);

        return view('inventory.itemmaster.itemmasterlist.create', [
            'categories' => ItemCategories::whereNull('ParentId')
                ->whereHas('status', fn ($q) => $q->where('Description', 'Active'))
                ->orderBy('Name', 'asc')
                ->get(),
            'status' => CodeDetail::where('CodeID', 'ItemStatus')
                ->orderBy('Description', 'asc')
                ->get(),
            'uoms' => UnitOfMeasure::where('Active', 1)
                ->orderBy('Code', 'asc')
                ->get(),
            'price' => PriceManagement::all(),
            'itemTypes' => ItemType::with(['type' => function ($query) {
                $query->orderBy('Description', 'asc');
            }])
                ->where('Active', 1)
                ->get()
                ->sortBy('type.Description'),
            'inventoryTypes' => InventoryType::with(['type' => function ($query) {
                $query->orderBy('Description', 'asc');
            }])
                ->where('Status', 1)
                ->get()
                ->sortBy('type.Description'),
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new ItemMasterListImport();

            Excel::import($import, $request->file('file'));

            $sheets = $import->sheets();
            $itemsSheet = $sheets['Items'] ?? null;

            if (! $itemsSheet) {
                foreach ($sheets as $sheetName => $sheet) {
                    if (strtolower($sheetName) === 'items') {
                        $itemsSheet = $sheet;

                        break;
                    }
                }

                if (! $itemsSheet) {
                    throw new \Exception('Could not find the Items sheet in the import file.');
                }
            }

            $processed = $itemsSheet->getProcessedCount();
            $created = $itemsSheet->getCreatedCount();
            $updated = $itemsSheet->getUpdatedCount();
            $skipped = $itemsSheet->getSkippedCount();
            $errors = $itemsSheet->getErrors();

            $successMessage = "Import completed! Processed: {$processed} rows. Created: {$created} new items. Updated: {$updated} existing items. ";
            if ($skipped > 0) {
                $successMessage .= "Skipped: {$skipped} rows.";
            }

            if (! empty($errors)) {
                $errorMessage = "<strong>Some rows had errors:</strong><br>";
                foreach (array_slice($errors, 0, 20) as $error) {
                    $errorMessage .= "• {$error}<br>";
                }
                if (count($errors) > 20) {
                    $errorMessage .= "<br>... and " . (count($errors) - 20) . " more errors.";
                }

                return back()->with('warning', $successMessage)->with('error_details', $errorMessage);
            }

            return back()->with('success', $successMessage);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $errors = collect($e->failures())->map(function ($failure) {
                $row = $failure->row();
                $errors = implode(', ', $failure->errors());

                return "Row {$row}: {$errors}";
            })->implode('<br>');

            return back()->with('error', "Validation errors:<br>{$errors}");
        } catch (\Exception $e) {
            $errorMessage = config('app.debug')
                ? "Import failed: " . $e->getMessage()
                : "Import failed. Please check the file format and try again.";

            return back()->with('error', $errorMessage);
        }
    }

    public function export()
    {
        return Excel::download(new ItemMasterListExport(), 'ItemMasterList.xlsx');
    }

    public function store(ItemMasterListRequest $request)
    {
        $this->authorize('create', ItemMasterList::class);

        $validated = $request->validated();
        $document = $request->file('Document');
        $image = $request->file('ImageUpload');
        $validated['Status'] = CodeDetail::where('CodeID', 'ItemStatus')
            ->where('Description', 'Active')
            ->value('Id');

        $this->service->create($validated, $image, $document);

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
                ->whereHas('status', fn ($q) => $q->where('Description', 'Active'))
                ->orderBy('Name', 'asc')
                ->get(),
            'status' => CodeDetail::where('CodeID', 'ItemStatus')
                ->orderBy('Description', 'asc')
                ->get(),
            'subcategories' => ItemCategories::where('ParentId', $item->category?->ParentId ?? $item->Category)
                ->whereHas('status', fn ($q) => $q->where('Description', 'Active'))
                ->orderBy('Name', 'asc')
                ->get(),
            'itemTypes' => ItemType::with(['type' => function ($query) {
                $query->orderBy('Description', 'asc');
            }])
                ->where('Active', 1)
                ->get()
                ->sortBy('type.Description'),
            'uoms' => UnitOfMeasure::where('Active', 1)
                ->orderBy('Code', 'asc')
                ->get(),
            'inventoryTypes' => InventoryType::with(['type' => function ($query) {
                $query->orderBy('Description', 'asc');
            }])
                ->where('Status', 1)
                ->get()
                ->sortBy('type.Description'),
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

        if ($request->has('remove_image') && $request->input('remove_image') == '1') {
            if ($item->ImageId) {
                \App\Models\DMS\Image::destroy($item->ImageId);
                $item->ImageId = null;
            }
        }

        $this->service->update($Id, $validated, $image, $document);

        return redirect()->route('itemmaster.index')->with('success', 'Item updated successfully.');
    }

    public function destroy($Id)
    {
        $item = ItemMasterList::findOrFail($Id);
        $this->authorize('delete', $item);

        if ($item->inUse()) {
            return redirect()->route('itemmaster.index')
                ->with('error', 'Cannot delete this item because it is currently in use.');
        }

        $this->service->delete($item);

        return redirect()->route('itemmaster.index')->with('success', 'Item deleted successfully.');
    }

    public function getSubcategories(Request $request)
    {
        return response()->json(
            ItemCategories::where('ParentId', $request->get('category_id'))
                ->whereHas('status', fn ($q) => $q->where('Description', 'Active'))
                ->orderBy('Name', 'asc')
                ->get(['Id', 'Name'])
        );
    }
}
