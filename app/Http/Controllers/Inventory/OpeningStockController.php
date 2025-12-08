<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\OpeningStockSampleExport;
use App\Http\Controllers\Controller;
use App\Imports\OpeningStockImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Validators\ValidationException;

class OpeningStockController extends Controller
{

    public function create()
    {
        return view('inventory.stockmanagement.openingstockload.create');
    }

    public function downloadSampleTemplate()
    {
        return Excel::download(new OpeningStockSampleExport, 'opening_stock_sample.xlsx');
    }

    public function uploadExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new OpeningStockImport, $request->file('excel_file'));

            return redirect()->route('sku.index')
                ->with('success', 'File uploaded successfully.');
        }

        catch (ValidationException $e) {
            $failures = $e->failures();

            return back()->with('error', 'Import failed. Some rows contain invalid or missing data.')
                        ->with('failures', $failures);
        }

        catch (\Exception $e) {
            return back()->with('error', 'Invalid or corrupted Excel file! Please upload a valid Opening Stock template.');
        }
    }

}
