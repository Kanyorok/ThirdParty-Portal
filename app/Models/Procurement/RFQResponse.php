<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class RFQResponse extends Model
{
    protected $table = 't_RFQResponse';
    protected $primaryKey = 'Id';
    public $timestamps = true;

    protected $fillable = [
        'RFQId',
        'RFQResponseNumber',
        'RFQNumber',
        'Quantity',
        'QuotedPrice',
        'TotalPayable',
        'Currency',
        'DurationDays',
        'Description',
        'SupplierName',
        'RequisitionItems',
        'RFQId',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    protected $casts = [
        'ResponseDetails' => 'array', // Assuming ResponseDetails is a JSON field
    ];

    public function rfq()
    {
        return $this->belongsTo(RFQ::class, 'RFQId', 'Id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'SupplierId', 'Id');
    }
}
