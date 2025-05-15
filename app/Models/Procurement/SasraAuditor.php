<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class SasraAuditor extends Model
{
    protected $table = 't_Auditors';

    protected $fillable = [
        'FirmName',
        'PhysicalAddress',
        'PostalAddress',
        'Town',
        'Status',
    ];
    protected $primaryKey = 'Id';

    public function engagements()
    {
        return $this->hasMany(EngagedAuditor::class, 'AuditorId');
    }
}
