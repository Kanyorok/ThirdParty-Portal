<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SegmentOrder extends Model
{
    use UserActorTrait,SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_FinanceSegmentOrder';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'SegmentType',
        'Description',

        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];

    protected $cast = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];
    public static function getPrimaryKey(): string
    {
        return 'FinanceSegmentOrderId';
    }
}
