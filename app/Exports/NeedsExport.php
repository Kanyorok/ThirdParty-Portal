<?php

namespace App\Exports;

use App\Models\Procurement\DepartmentNeed;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class NeedsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = DepartmentNeed::with(['item', 'branch', 'department']);

        if ($this->request->branch) {
            $query->where('BranchID', $this->request->branch);
        }

        if ($this->request->department) {
            $query->where('DepartmentID', $this->request->department);
        }

        if ($this->request->year && $this->request->year !== "All Years") {
            $query->whereYear('RequestedDate', $this->request->year);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Item Name',
            'Branch',
            'Department',
            'Requested Qty',
            'Estimated Cost',
            'Date Needed',
            'Status',
        ];
    }

    public function map($need): array
    {
        return [
            $need->item->ItemName ?? 'N/A',
            $need->branch->Name ?? 'N/A',
            $need->department->Name ?? 'N/A',
            $need->RequestedQty,
            number_format($need->RequestedQty * $need->EstimatedUnitCost, 2),
            \Carbon\Carbon::parse($need->RequestedDate)->format('d/m/Y'),
            $need->Status?->label() ?? 'Unknown',


        ];
    }
}
