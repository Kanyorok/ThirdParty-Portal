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

            $processed = $import->getProcessedCount();
            $created = $import->getCreatedCount();
            $updated = $import->getUpdatedCount();
            $skipped = $import->getSkippedCount();
            $errors = $import->getErrors();

            $successMessage = "Import completed! ";
            $successMessage .= "Processed: {$processed} rows. ";

            if ($created > 0) {
                $successMessage .= "Created: {$created} new items. ";
            }

            if ($updated > 0) {
                $successMessage .= "Updated: {$updated} existing items. ";
            }

            if ($skipped > 0) {
                $successMessage .= "Skipped: {$skipped} rows. ";
            }

            $importResult = [
                'message' => $successMessage,
                'summary' => [
                    'processed' => $processed,
                    'created' => $created,
                    'updated' => $updated,
                    'skipped' => $skipped,
                ],
                'errors' => $errors,
                'warnings' => [],
            ];

            if (! empty($errors)) {
                $errorMessage = "<strong>Some rows had errors:</strong><br>";

                foreach (array_slice($errors, 0, 20) as $error) {
                    $errorMessage .= "• {$error}<br>";
                }
                if (count($errors) > 20) {
                    $errorMessage .= "<br>... and " . (count($errors) - 20) . " more errors.";
                }

                return redirect()->route('itemmaster.index')
                    ->with('import_result', $importResult)
                    ->with('error_details', $errorMessage);
            }

            return redirect()->route('itemmaster.index')
                ->with('import_result', $importResult);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $errors = collect($e->failures())->map(function ($failure) {
                $row = $failure->row();
                $errors = implode(', ', $failure->errors());

                return "Row {$row}: {$errors}";
            })->toArray();

            $importResult = [
                'message' => "Import failed due to validation errors.",
                'summary' => [
                    'processed' => 0,
                    'created' => 0,
                    'updated' => 0,
                    'skipped' => count($errors),
                ],
                'errors' => $errors,
                'warnings' => [],
            ];

            return redirect()->route('itemmaster.index')
                ->with('import_result', $importResult);
        } catch (\Exception $e) {
            $importResult = [
                'message' => config('app.debug')
                    ? "Import failed: " . $e->getMessage()
                    : "Import failed. Please check the file format and try again.",
                'summary' => [
                    'processed' => 0,
                    'created' => 0,
                    'updated' => 0,
                    'skipped' => 0,
                ],
                'errors' => ["General error: " . $e->getMessage()],
                'warnings' => [],
            ];

            return redirect()->route('itemmaster.index')
                ->with('import_result', $importResult);
        }
    }

    public function export()
    {
        return Excel::download(
            new ItemMasterListExport(),
            'ItemMasterList_' . now()->format('Y-m-d_His') . '.xlsx'
        );
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
                // @phpstan-ignore-next-line - Image model is deprecated but still functional
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
