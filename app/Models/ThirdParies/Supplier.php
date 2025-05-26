<?php

namespace App\Models\ThirdParies;

use App\Models\Inventory\ItemCategories;
use App\Models\Procurement\ProcurementPeriod;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQEvaluation;
use App\Models\Procurement\RFQLine;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{


    protected $table = 't_Suppliers';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

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

    public function getIsPrequalifiedAttribute($value)
    {
        return (bool)$value;
    }

    public function setIsPrequalifiedAttribute($value)
    {
        $this->attributes['IsPrequalified'] = (bool)$value;
    }

    public function category()
    {
        return $this->belongsTo(ItemCategories::class, 'CategoryId', 'Id');
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
        return $this->hasMany(RFQLine::class, 'SupplierId');
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 't_RFQ_Supplier', 'RFQId', 'SupplierId')
            ->withPivot('Status')
            ->withTimestamps();
    }

}
