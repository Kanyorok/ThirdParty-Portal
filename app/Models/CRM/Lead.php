<?php

namespace App\Models\CRM;

use App\Enums\Employee\GenderEnum;
use App\Enums\LeadStatusEnum;
use App\Enums\LeadTypeEnum;
use App\Models\Auth\User;
use App\Models\Communication\Call;
use App\Models\Communication\Email;
use App\Models\Communication\SMS;
use App\Models\Core\Activity;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Country;
use App\Models\Core\Locality;
use App\Models\Core\Task;
use App\Models\DMS\Image;
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
    use ImageTrait;
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_Leads';
    protected $primaryKey = 'LeadID';

    public static function getPrimaryKey(): string
    {
        return 'LeadID';
    }
    protected $fillable = [
        "Name", "Email", "Phone", "Website", "Gender", "Status", "RelationshipManagerID", "LocationID", "CountryId", "ImageId",
        "LeadLossReason", "Industry", "Source", "JobTitle", "OtherNames", "LastContacted", "CustomerType", "Type", "Website", 'Notes',
        'ApplicationID', 'ArchivedOn', 'ArchivedBy', 'CreatedBy', 'ModifiedBy', 'DeletedBy',
                          ];

    protected $casts = [
        'Status' => LeadStatusEnum::class,
        'Gender' => GenderEnum::class,
        'Type' => LeadTypeEnum::class,
        'LastContacted' => 'datetime',
        'ArchivedOn' => 'datetime',
        'RelationshipManagerID' => 'integer',
        'CountryId' => 'integer',
    ];

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'ImageId', 'ImageID');
    }

    public function lossReason(): BelongsTo
    {
        return $this->belongsTo(CodeDetail::class, 'LeadLossReason', 'ID')->where('CodeID', 'LeadLossReason');
    }

    public function ind(): BelongsTo
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

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'CountryId', 'Id');
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
        return $this->morphMany(Email::class, 'party', "Party", "PartyID", 'LeadID');
    }

    public function crmsms(): MorphMany
    {
        return $this->morphMany(SMS::class, 'party', "Party", "PartyID", 'LeadID');
    }

    protected function getImageName(): string
    {
        return $this->Name;
    }
}
