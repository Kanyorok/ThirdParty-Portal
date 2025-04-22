<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 't_Suppliers';

    protected $fillable = [
        'SupplierName',
        'ContactEmail',
        'ContactPhone',
        'Address',
        'IsPrequalified',
        'CategoryId',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $primaryKey = 'Id';

    public function getIsPrequalifiedAttribute($value)
    {
        return (bool) $value;
    }

    public function setIsPrequalifiedAttribute($value)
    {
        $this->attributes['IsPrequalified'] = (bool) $value;
    }

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'CategoryId', 'id');
    }

    public function categories()
    {
        return $this->belongsToMany(ItemCategory::class, 't_SupplierCategory');
    }

    public function ProcurementPeriods()
    {
        return $this->belongsToMany(ProcurementPeriod::class, 't_ProcurementPeriodSupplier', 'SupplierId', 'ProcurementPeriodId');
    }

    public function rfqs()
    {
        return $this->hasMany(RFQ::class, 'SupplierId');
    }

}
