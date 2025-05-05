<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use App\Models\Procurement\RFQ;

class RFQEvaluation extends Model
{
    protected $table = 't_RFQEvaluation';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'RFQId',
        'RFQNumber',
        'RequisitionItems',
        'SupplierName',
        'Currency',
        'DurationDays',
        'TotalPayable',
        'Comments',
        'CreatedBy',
        'CreatedOn',
        'ModifiedOn',
    ];

    protected $casts = [
        'RequisitionItems' => 'array', // Assuming this is a JSON field
        'CreatedOn' => 'datetime',
    ];

    public function rfq()
    {
        return $this->belongsTo(RFQ::class, 'RFQId', 'Id');
    }
}
