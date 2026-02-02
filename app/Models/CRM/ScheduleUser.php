<?php

namespace App\Models\CRM;

use App\Enums\ScheduleUserStatusEnum;
use App\Models\Auth\User;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ScheduleUser extends Pivot
{
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';

    protected $table = 't_ScheduleUsers';

    public static function getPrimaryKey(): string
    {
        return 'ScheduleUsersId';
    }

    protected $fillable = [
                           'ScheduleId',
                           'UserID',
                           'ScheduleUserStatus',
                           'DecidedOn',
                           'CreatedOn',
                           'CreatedBy',
                           'ModifiedOn',
                           'ModifiedBy',
                           'ReminderOn',
                          ];

    protected $casts = [
                        'DecidedOn' => 'datetime',
                        'ReminderOn' => 'datetime',
                        'ScheduleUserStatus' => ScheduleUserStatusEnum::class,
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
