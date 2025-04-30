<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class RFQResponse extends Model
{
    protected $table = 't_RFQResponse';
    protected $primaryKey = 'Id';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'RFQId',
        'RFQResponseNumber',
        'RFQNumber',
        'SupplierName',
        'TotalPayable',
        'Currency',
        'DurationDays',
        'RequisitionItems',
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
