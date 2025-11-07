<?php

namespace App\Services\Procurement;

use App\Models\Procurement\Item;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use App\Exports\ItemsExport;

class ItemExportService
{
    public function download(array $filters, string $format = 'xlsx')
    {
        $query = Item::with('category');

        if (!empty($filters['category_id'])) {
            $query->where('CategoryId', $filters['category_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('Type', $filters['type']);
        }

        $items = $query->get();

        if ($format === 'pdf') {
            $pdf = PDF::loadView('procurement.items.export_pdf', compact('items'));
            return $pdf->download('items_filtered.pdf');
        }

        return Excel::download(new ItemsExport($items), 'items_filtered.xlsx');
    }
}
