<?php

namespace App\Models\Procurement;

use App\Models\Inventory\ItemCategories;
use App\Models\ThirdParies\Supplier;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RFQ extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $table = 't_RFQ';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';


    public static function getPrimaryKey(): string
    {
        return 'RFQId';
    }

    protected $fillable = [
        'RFQNumber', 'RequisitionId', 'Comments', 'Status', 'SubmissionDeadline', 'CreatedBy', 'ModifiedBy','Remarks'
    ];

    public function rfqLines()
    {
        return $this->hasMany(RFQLine::class, 'RFQId');
    }


    public function category()
    {
        return $this->belongsTo(ItemCategories::class, 'ItemCategoryId');
    }

    public function rfqResponses()
    {
        return $this->hasMany(RFQResponse::class, 'RFQId', 'Id');
    }


    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 't_RFQ_Supplier', 'RFQId', 'SupplierId')
                    ->withPivot('Status')
                    ->withTimestamps();
    }
    public function requisition()
    {
        return $this->belongsTo(Requisitions::class, 'RequisitionId', 'Id');
    }

}
