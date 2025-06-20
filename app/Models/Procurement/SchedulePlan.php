<?php

namespace App\Models\Procurement;

use App\Enums\Procurement\SchedulePlanEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchedulePlan extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $table = 't_SchedulePlan';
    protected $primaryKey = 'Id';


    protected $fillable = [
        'ScheduleId', 'PlanId', 'PlanLineId', 'ScheduleQTY', 'ScheduleType', 'Status',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'Id';
    }

    protected $casts = [
        'Status' => SchedulePlanEnum::class,
    ];

    public function periods()
    {
        return $this->hasMany(SchedulePeriod::class, 'ScheduleId', 'Id');
    }
}
