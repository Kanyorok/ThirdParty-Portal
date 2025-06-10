<?php

namespace App\Models\Procurement;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Requisitions extends Model
{
    //
    use SoftDeletes;
    use UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_Requisitions';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'RequisitionID';
    }

    protected $fillable = [
        'RequisitionNo', 'Branch', 'Department', 'NeededBy', 'Remarks', 'Category',
         'CreatedBy', 'ModifiedBy', 'DeletedBy', 'CategoryId'
    ];

    protected $casts = [
        // 'Status' => CampaignStatusEnum::class,
        // 'Type' => CampaignTypeEnum::class,
        'CreatedBy'  => 'integer',
        'ModifiedBy' => 'integer',//,
        // 'Processing' => 'boolean'
    ];
    public function requisitionLines()
    {
        return $this->hasMany(RequisitionLine::class, 'RequisitionID', 'Id');
    }

}
