<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\OpeningStockSampleExport;
use App\Http\Controllers\Controller;
use App\Imports\OpeningStockImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

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

        Excel::import(new OpeningStockImport, $request->file('excel_file'));

        return redirect()->route('sku.index')
            ->with('success', 'File uploaded successfully. Import is processing in the background.');
    }

}
