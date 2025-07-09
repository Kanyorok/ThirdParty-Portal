<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchedulePeriod extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_SchedulePeriod';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    public static function getPrimaryKey(): string
    {
        return 'SchedulePeriodId';
    }

    protected $fillable = [
        'ScheduleId',
        'SchedulePeriod',
        'ScheduleQTY',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];
}

