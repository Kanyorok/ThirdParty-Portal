<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\PriceManagementExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\PriceManagementRequest;
use App\Imports\PriceManagementImport;
use App\Models\Core\Currency;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\PriceManagement;
use App\Models\Inventory\UnitOfMeasure;
use App\Services\Inventory\PriceManagementService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PriceManagementController extends Controller
{
    protected $priceService;

    public function __construct(PriceManagementService $priceService)
    {
        $this->priceService = $priceService;
    }

    public function index()
    {
        $prices = $this->priceService->list();
        $currencies = Currency::all();
        $uoms = UnitOfMeasure::all();
        $items = ItemMasterList::with('uom')
            ->whereNotIn('Id', function ($query) {
                $query->select('ItemID')
                    ->from('t_Pricing')
                    ->whereNull('DeletedOn');
            })
            ->get();

        return view('inventory.pricemanagement.index', compact('prices', 'items', 'currencies', 'uoms'));
    }

    public function create()
    {
        $this->authorize('create', PriceManagement::class);

        $items = ItemMasterList::all();
        $uoms = UnitOfMeasure::all();
        $currencies = Currency::all();

        return view('inventory.pricemanagement.index', compact('items', 'uoms', 'currencies'));
    }

    public function store(PriceManagementRequest $request)
    {
        $this->authorize('create', PriceManagement::class);

        $this->priceService->create($request->validated());

        return redirect()->route('pricemanagement.index')
            ->with('success', 'Price created and assigned to item!');
    }

    public function edit($id)
    {
        $price = PriceManagement::findOrFail($id);
        $this->authorize('update', PriceManagement::class);

        $items = ItemMasterList::all();
        $uoms = UnitOfMeasure::all();
        $currencies = Currency::all();

        return view('inventory.pricemanagement.edit', compact('price', 'items', 'uoms', 'currencies'));
    }

    public function update(PriceManagementRequest $request, $id)
    {
        $oldPrice = PriceManagement::findOrFail($id);
        $this->authorize('update', PriceManagement::class);

        $oldPrice->update([
            'DeletedOn' => now(),
            'DeletedBy' => auth()->id(),
        ]);

        $data = $request->validated();
        $data['PriceID'] = $oldPrice->PriceID;
        $data['ItemID'] = $oldPrice->ItemID;
        $data['UOM'] = $oldPrice->UOM;

        $this->priceService->create($data);

        return redirect()->route('pricemanagement.index')
            ->with('success', 'Price updated (new version created)!');
    }

    public function destroy($id)
    {
        $price = PriceManagement::findOrFail($id);
        $this->authorize('destroy', PriceManagement::class);

        $this->priceService->delete($price);

        return redirect()->route('pricemanagement.index')
            ->with('success', 'Price deleted.');
    }

    public function downloadSampleTemplate()
    {
        return Excel::download(
            new PriceManagementExport(),
            'PriceManagement_' . now()->format('Y-m-d_His') . '.xlsx'
        );
    }

    public function importPricing(Request $request)
    {
        $this->authorize('update', PriceManagement::class);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls|max:2048',
        ]);

        try {
            $import = new PriceManagementImport();

            Excel::import($import, $request->file('file'));

            $processed = $import->getProcessedCount();
            $created = $import->getCreatedCount();
            $updated = $import->getUpdatedCount();
            $skipped = $import->getSkippedCount();
            $errors = $import->getErrors();
            $successParts = [];
            
            if ($created > 0) {
                $successParts[] = "Created: {$created} new price" . ($created > 1 ? 's' : '');
            }
            
            if ($updated > 0) {
                $successParts[] = "Updated: {$updated} existing price" . ($updated > 1 ? 's' : '');
            }
            
            if ($skipped > 0) {
                $successParts[] = "Skipped: {$skipped} row" . ($skipped > 1 ? 's' : '') . " (empty/invalid data)";
            }

            if (empty($successParts)) {
                $successMessage = "No changes were made. All rows were either empty or unchanged.";
            } else {
                $successMessage = "Import completed! " . implode('. ', $successParts) . '.';
            }
            if (!empty($errors)) {
                $errorMessage = "<strong>Some rows had critical errors:</strong><br>";
                
                foreach (array_slice($errors, 0, 20) as $error) {
                    $errorMessage .= "• {$error}<br>";
                }

                if (count($errors) > 20) {
                    $errorMessage .= "<br>... and " . (count($errors) - 20) . " more errors.";
                }

                return back()
                    ->with('warning', $successMessage)
                    ->with('error_details', $errorMessage);
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
}
