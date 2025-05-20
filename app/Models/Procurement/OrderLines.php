<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class OrderLines extends Model
{
    //

    use SoftDeletes;
    use UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_OrderLines';
    protected $primaryKey = 'Id';


    public static function getPrimaryKey(): string
    {
        return 'OrderLineID';
    }
    protected $casts = [
        // 'Status' => CampaignStatusEnum::class,
        // 'Type' => CampaignTypeEnum::class,
        'CreatedBy'  => 'integer',
        'ModifiedBy' => 'integer',//,
        // 'Processing' => 'boolean'
    ];
}
