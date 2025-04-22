<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class ProcurementPlan extends Model
{
    protected $table = 't_ProcurementPlans';

    protected $fillable = [
        'ProcurementPeriodId',
        'ItemId',
        'Quantity',
        'TotalCost',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $primaryKey = 'Id';

    public function item()
    {
        return $this->belongsTo(Item::class, 'ItemId');
    }

    public function procurementPeriod()
    {
        return $this->belongsTo(ProcurementPeriod::class, 'ProcurementPeriodId');
    }
}
