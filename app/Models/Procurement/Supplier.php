<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory; 
    
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

    public function rfqEvaluations()
    {
        return $this->hasMany(RFQEvaluation::class, 'SupplierId');
    }

    public function ProcurementPeriods()
    {
        return $this->belongsToMany(ProcurementPeriod::class, 't_ProcurementPeriodSupplier', 'SupplierId', 'ProcurementPeriodId');
    }

    public function rfqs()
    {
        return $this->hasMany(RFQ::class, 'SupplierId');
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 't_RFQ_Supplier', 'RFQId', 'SupplierId')
                    ->withPivot('Status')
                    ->withTimestamps();
    }

}
