<?php

namespace App\Exports\HR;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StatutoryReturnExport implements FromArray, WithHeadings
{
    private array $columns;
    private array $rows;

    public function __construct(array $columns, array $rows)
    {
        $this->columns = $columns;
        $this->rows = $rows;
    }

    public function headings(): array
    {
        return array_map(function ($column) {
            return $column['label'] ?? '';
        }, $this->columns);
    }

    public function array(): array
    {
        $data = [];
        foreach ($this->rows as $row) {
            $data[] = array_map(function ($column) use ($row) {
                $key = $column['key'] ?? '';
                return $row[$key] ?? '';
            }, $this->columns);
        }

        return $data;
    }
}
