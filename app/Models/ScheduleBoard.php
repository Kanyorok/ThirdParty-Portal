<?php

namespace App\Models;

use App\Enums\ScheduleUserStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ScheduleBoard extends Pivot
{
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    protected $connection = 'sqlsrv';
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
