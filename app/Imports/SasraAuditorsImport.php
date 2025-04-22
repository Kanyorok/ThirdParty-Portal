<?php

namespace App\Imports;

use App\Models\Procurement\SasraAuditor;
use Maatwebsite\Excel\Concerns\ToModel;

class SasraAuditorsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new SasraAuditor([
            'FirmName' => $row[0],
            'PhysicalAddress' => $row[1],
            'PostalAddress' => $row[2],
            'Town' => $row[3],
        ]);
    }
}
