<?php

namespace App\Models;

use App\Enums\ScheduleStatusEnum;
use App\Models\BR\Client;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_Schedule';
    protected $primaryKey = 'ScheduleID';

    protected $fillable = [
                           'Title',
                           'Notes',
                           'ScheduledType',
                           'ScheduledTypeID',
                           'ScheduleStatusID',
                           'StartOn',
                           'EndOn',
                           'Type',
                           'Source',
                           'SourceID',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    protected $casts = [
                        'StartOn'          => 'datetime',
                        'EndOn'            => 'datetime',
                        'ScheduleStatusID' => ScheduleStatusEnum::class,
                       ];


    // (Call, Meeting, Task)
    public function scheduled(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'ScheduledType', 'ScheduledTypeID');
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 't_ScheduleClients', 'ScheduleId', 'ClientID', 'ScheduleID')
            ->withPivot(['CreatedBy', 'ModifiedBy'])->withTimestamps('CreatedOn', 'ModifiedOn')
            ->using(ScheduleClient::class);
    }

    public function scheduleClients(): HasMany
    {
        return $this->hasMany(ScheduleClient::class, 'ScheduleId', 'ScheduleID');
    }


    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class, 't_ScheduleLeads', 'ScheduleId', 'LeadId', 'ScheduleID')
            ->withPivot(['CreatedBy', 'ModifiedBy'])->withTimestamps('CreatedOn', 'ModifiedOn')
            ->using(ScheduleClient::class);
    }

    /**
     * Board Members
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Board::class, 't_ScheduleBoard', 'ScheduleId', 'BoardMemberId', 'ScheduleID', 'Id')
            ->withTimestamps('CreatedOn', 'ModifiedOn')->withPivot(['CreatedBy', 'ModifiedBy', 'ScheduleStatus', 'DecidedOn'])
            ->using(ScheduleBoard::class);
    }

    public function scheduleLeads(): HasMany
    {
        return $this->hasMany(ScheduleLead::class, 'ScheduleId', 'ScheduleID');
    }


    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 't_ScheduleUsers', 'ScheduleId', 'UserID')
            ->withPivot(['CreatedBy', 'ModifiedBy', 'ScheduleUserStatus', 'DecidedOn'])->withTimestamps('CreatedOn', 'ModifiedOn')
            ->using(ScheduleUser::class);
    }

    public function scheduleUsers(): HasMany
    {
        return $this->hasMany(ScheduleUser::class, 'ScheduleId', 'ScheduleID');
    }

    public function source(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Source", "SourceID");
    }
}
