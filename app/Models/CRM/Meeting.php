<?php

namespace App\Models\CRM;

use App\Enums\MeetingStatusEnum;
use App\Enums\Schedule\MeetingLocationEnum;
use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\DMS\Image;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meeting extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_Meetings';
    protected $primaryKey = 'MeetingID';
    protected $fillable = [
        'Title',
        'StartOn',
        'EndOn',
        'Location',
        'Notes',
        'Type',
        'StatusID',
        'MeetingLocationType',
        'LocationId',
        'Source',
        'SourceID',
        'CreatedBy',
        'DeletedBy',
        'ModifiedBy',
    ];
    protected $casts = [
        'StartOn' => 'datetime',
        'EndOn' => 'datetime',
        'StatusID' => MeetingStatusEnum::class,
        'MeetingLocationType' => MeetingLocationEnum::class,
    ];

    public static function getPrimaryKey(): string
    {
        return 'MeetingID';
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 't_MeetingClients', 'ClientID', 'MeetingId')
            ->withPivot(['CreatedOn', 'CreatedBy', 'ModifiedOn', 'ModifiedBy'])
            ->using(MeetingClient::class);
    }

    public function meetingClients(): HasMany
    {
        return $this->hasMany(MeetingClient::class, 'MeetingId', 'MeetingID');
    }

    public function meetingLeads(): HasMany
    {
        return $this->hasMany(MeetingLead::class, 'MeetingId', 'MeetingID');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(MeetingRoom::class, 'LocationId', 'Id');
    }

    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class, 't_MeetingLeads', 'ScheduleId', 'LeadId', 'MeetingId')
            ->withPivot(['CreatedBy', 'ModifiedBy'])->withTimestamps()
            ->using(MeetingLead::class);
    }

    public function meetingUsers(): HasMany
    {
        return $this->hasMany(MeetingUser::class, 'MeetingId', 'MeetingID');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 't_MeetingUsers', 'MeetingId', 'UserID', 'MeetingID')
            ->withPivot(['CreatedBy', 'ModifiedBy'])->withTimestamps();
        //->using(MeetingUser::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Source", "SourceID");
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Image::class, __FUNCTION__, "ImageType", "ImageTypeID", 'MeetingID');
    }
}
