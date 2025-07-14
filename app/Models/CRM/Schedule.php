<?php

namespace App\Models\CRM;

use App\Enums\ScheduleStatusEnum;
use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\ThirdParies\Board;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

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
        'StartOn' => 'datetime',
        'EndOn' => 'datetime',
        'ScheduleStatusID' => ScheduleStatusEnum::class,
    ];

    public static function getPrimaryKey(): string
    {
        return 'ScheduleID';
    }


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
    /**
 * Safely get the first associated client ID or throw an error.
 */
public function firstClientIdOrFail(): int
{
    $client = $this->scheduleClients()->first();

    if (!$client) {
        throw new \RuntimeException("No client found for ScheduleID {$this->ScheduleID}");
    }

    return $client->ClientID;
}
public function resolveParty(): array
{
    if ($this->scheduleClients()->exists()) {
        return [
            'party' => \App\Models\BR\Client::getPrimaryKey(),
            'party_id' => $this->scheduleClients()->first()->ClientID,
        ];
    }

    if ($this->scheduleLeads()->exists()) {
        return [
            'party' => \App\Models\CRM\Lead::getPrimaryKey(),
            'party_id' => $this->scheduleLeads()->first()->LeadId,
        ];
    }

    throw new \RuntimeException("No associated Client or Lead found for ScheduleID {$this->ScheduleID}");
}


}
