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

            $successMessage = "Import completed! Processed: {$processed} rows. ";
            $successMessage .= "Created: {$created} new prices. ";
            $successMessage .= "Updated: {$updated} existing prices. ";

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
