<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class EngagedAuditor extends Model
{
    protected $table = 't_EngagedAuditors';

    protected $fillable = [
        'SasraAuditorId',
        'EngagementStartDate',
        'EngagementEndDate',
        'EngagementStatus',
        'CreatedBy',
        'ModifiedBy',
    ];
    protected $primaryKey = 'Id';

    public function auditor()
    {
        return $this->belongsTo(SasraAuditor::class, 'SasraAuditorId');
    }
}
