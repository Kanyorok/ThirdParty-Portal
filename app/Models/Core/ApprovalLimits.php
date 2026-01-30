<?php

namespace App\Models\Core;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalLimits extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';

    protected $table = 't_ApprovalLimits';
    protected $primaryKey = 'Id';


    protected $fillable = [
        'DocType',
        'MaxAmount',
        'Permission',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        // 'Status' => CampaignStatusEnum::class,
        // 'Type' => CampaignTypeEnum::class,
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer',//,
        // 'Processing' => 'boolean'
    ];
}
