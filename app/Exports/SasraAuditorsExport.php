<?php

namespace App\Exports;

use App\Models\Procurement\SasraAuditor;
use Maatwebsite\Excel\Concerns\FromCollection;

class SasraAuditorsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return SasraAuditor::all();
    }
}
