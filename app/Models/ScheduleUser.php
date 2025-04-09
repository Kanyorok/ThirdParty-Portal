<?php

namespace App\Models;

use App\Enums\ScheduleUserStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ScheduleUser extends Pivot
{
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_ScheduleUsers';

    protected $fillable = [
        'ScheduleId', 'UserID', 'ScheduleUserStatus','DecidedOn',
        'CreatedOn', 'CreatedBy', 'ModifiedOn', 'ModifiedBy', 'ReminderOn'
    ];

    protected $casts=[
        'DecidedOn'=>'datetime',
        'ReminderOn' => 'datetime',
        'ScheduleUserStatus' => ScheduleUserStatusEnum::class
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserID', 'Id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'ScheduleId', 'ScheduleID');
    }
}
