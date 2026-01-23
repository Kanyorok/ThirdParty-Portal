<?php

namespace App\Models\Procurement;

use App\Models\Inventory\UnitOfMeasure;
use App\Models\ThirdParies\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RFQResponse extends Model
{
    use SoftDeletes;

    protected $table = 't_RFQResponse';
    protected $primaryKey = 'Id';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'RFQId',
        'RFQResponseNumber',
        'RFQNumber',
        'SupplierId',
        'SupplierName',
        'TotalPayable',
        'Currency',
        'DurationDays',
        'Status',
        'SubmittedOn',
        'SubmittedBy',
        'RequisitionItems',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    protected $casts = [
        'ResponseDetails' => 'array', // Assuming ResponseDetails is a JSON field
        'SubmittedOn' => 'datetime',
    ];

    public function rfq()
    {
        return $this->belongsTo(RFQ::class, 'RFQId', 'Id');
    }

    public function items()
    {
        return $this->hasMany(RFQResponseItem::class, 'RfqResponseId', 'Id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'SupplierId', 'Id');
    }

    public function uom()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'UOMId', 'Id');
    }


}
