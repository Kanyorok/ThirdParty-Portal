<?php

namespace App\Models\ERP;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequisitionLines extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_RequisitionLines';
    protected $primaryKey = 'Id';

    protected $fillable = [
                           'RequisitionID',
                           'Module',
                           'Item',
                           'Description',
                           'UOM',
                           'Quantity',
                           'ExpectedPrice',
                           'Urgency',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    public static function getPrimaryKey(): string
    {
        return 'RequisitionLineID';
    }

    protected $casts = [
        // 'Status' => CampaignStatusEnum::class,
        // 'Type' => CampaignTypeEnum::class,
                        'CreatedBy'  => 'integer',
                        'ModifiedBy' => 'integer',//,
        // 'Processing' => 'boolean'
                       ];
}
