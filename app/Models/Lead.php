<?php

namespace App\Models;

use App\Enums\GenderEnum;
use App\Enums\LeadStatusEnum;
use App\Enums\LeadTypeEnum;
use App\Traits\Model\ImageTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use ImageTrait, UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_Leads';
    protected $primaryKey = 'LeadID';

    protected $fillable = [
        "Name", "Email", "Phone", "Website", "Gender", "Status", "RelationshipManagerID", "LocationID", "ImageId", "LeadLossReason", "Industry", "Source", "JobTitle",
        "OtherNames", "LastContacted", "CustomerType", "Type", "Website", 'Notes', 'ApplicationID', 'ArchivedOn', 'ArchivedBy', 'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    protected $casts = [
        'Status' => LeadStatusEnum::class,
        'Gender' => GenderEnum::class,
        'Type' => LeadTypeEnum::class,
        'LastContacted' => 'datetime',
        'ArchivedOn' => 'datetime',
        'RelationshipManagerID' => 'integer',
    ];

    public function photo(): BelongsTo
    {
        return $this->belongsTo(CRMImage::class, 'ImageId', 'ImageID');
    }

    public function lossReason(): BelongsTo
    {
        return $this->belongsTo(CodeDetail::class, 'LeadLossReason', 'ID')->where('CodeID', 'LeadLossReason');
    }

    public function industry(): BelongsTo
    {
        return $this->belongsTo(CodeDetail::class, 'Industry', 'ID')->where('CodeID', 'Industries');
    }

    public function customerType(): BelongsTo
    {
        return $this->belongsTo(CodeDetail::class, 'CustomerType', 'ID')->where('CodeID', 'CustomerTypes');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(CodeDetail::class, 'Source', 'ID')->where('CodeID', 'MarketingModes');
    }

    public function RelationshipManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'RelationshipManagerID', 'Id')->withTrashed();
    }

    public function watchers(): HasMany
    {
        return $this->hasMany(LeadUser::class, 'LeadId', 'LeadID');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Locality::class, 'LocationID', 'ID');
    }

    public function calls(): MorphMany
    {
        return $this->morphMany(Call::class, 'party', "Party", "PartyID", 'LeadID');
    }

    public function meetings(): BelongsToMany
    {
        return $this->belongsToMany(Meeting::class, 't_MeetingLeads', 'LeadId', 'MeetingId', 'LeadID', 'MeetingID')
            ->withTimestamps('CreatedOn', 'ModifiedOn')->withPivot(['CreatedBy', 'ModifiedBy']);//->using(MeetingLead::class);
    }

    public function contacts(): MorphMany
    {
        return $this->morphMany(Contact::class, 'party', "Party", "PartyID", 'LeadID');
    }

    public function discussions(): MorphMany
    {
        return $this->morphMany(Discussion::class, 'party', "Party", "PartyID", 'LeadID');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Notes::class, 'party', "Party", "PartyID", 'LeadID');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'party', "Party", "PartyID", 'LeadID');
    }

    public function tickets(): MorphMany
    {
        return $this->morphMany(Ticket::class, 'party', "Party", "PartyID", 'LeadID');
    }

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class, 't_ScheduleLeads', 'LeadId', 'ScheduleId', 'LeadID', 'ScheduleID')
            ->withTimestamps('CreatedOn', 'ModifiedOn')->withPivot(['CreatedBy', 'ModifiedBy']);
    }

    public function products(): HasMany
    {
        return $this->hasMany(LeadProduct::class, 'LeadId', 'LeadID');
    }


    public function marketingLists(): BelongsToMany
    {
        return $this->belongsToMany(MarketingList::class, 't_MarketingListParties', 'PartyID', 'MarketingListId', 'LeadID', 'MarketingListID')
            ->withPivot(['CreatedOn', 'CreatedBy', 'ModifiedOn', 'ModifiedBy'])->withPivotValue('t_MarketingListParties.Party', self::getPrimaryKey());
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'party', "Party", "PartyID", 'LeadID');
    }

    public function crmmails(): MorphMany
    {
        return $this->morphMany(CrmEmail::class, 'party', "Party", "PartyID", 'LeadID');
    }

    public function crmsms(): MorphMany
    {
        return $this->morphMany(CrmSMS::class, 'party', "Party", "PartyID", 'LeadID');
    }

}
