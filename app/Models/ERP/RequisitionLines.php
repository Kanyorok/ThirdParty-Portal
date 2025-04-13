<?php

namespace App\Models\ERP;

use Illuminate\Database\Eloquent\Model;

class RequisitionLines extends Model
{
    use SoftDeletes, UserActorTrait;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_RequisitionLines';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'RequisitionID', 'Module', 'Item', 'Description', 'UOM', 'Quantity', 'ExpectedPrice',
        'Urgency', 'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    protected $casts = [
        // 'Status' => CampaignStatusEnum::class,
        // 'Type' => CampaignTypeEnum::class,
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer' //,
        // 'Processing' => 'boolean'
    ];



}
