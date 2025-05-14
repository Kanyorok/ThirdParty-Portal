<?php

namespace App\Models\CRM;

use App\Enums\ScheduleUserStatusEnum;
use App\Models\ThirdParies\Board;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ScheduleBoard extends Pivot
{
    use  UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';

    protected $table = 't_ScheduleBoard';

    protected $fillable = [
                           'ScheduleId',
                           'BoardMemberId',
                           'ScheduleStatus',
                           'DecidedOn',
                           'CreatedOn',
                           'CreatedBy',
                           'ModifiedOn',
                           'ModifiedBy',
                           'ReminderOn',
                          ];

    protected $casts = [
                        'DecidedOn'      => 'datetime',
                        'ReminderOn'     => 'datetime',
                        'ScheduleStatus' => ScheduleUserStatusEnum::class,
                       ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class, 'BoardMemberId', 'Id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'ScheduleId', 'ScheduleID');
    }
}
