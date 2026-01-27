<?php

namespace App\Models\BR;

use App\Models\Communication\Call;
use App\Models\Communication\Email;
use App\Models\Communication\SMS;
use App\Models\Core\Activity;
use App\Models\Core\Task;
use App\Models\CRM\Contact;
use App\Models\CRM\Discussion;
use App\Models\CRM\MarketingList;
use App\Models\CRM\Meeting;
use App\Models\CRM\MeetingClient;
use App\Models\CRM\Notes;
use App\Models\CRM\Review;
use App\Models\CRM\Schedule;
use App\Models\CRM\Ticket;
use App\Services\BR\ImageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Client extends Model
{
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';
    protected $primaryKey = 'ClientID';
    // protected $table = 't_Client';
    //  protected $connection = 'brcbs';
    protected $connection = 'sqlsrv';
    protected $table = 'syn_t_Client';

    public function photo(): BelongsTo
    {
        return $this->belongsTo(ImageAccount::class, 'PhotoID', 'ImageID');
    }

    public function signature(): BelongsTo
    {
        return $this->belongsTo(ImageAccount::class, 'SignID', 'ImageID');
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(BRUser::class, 'OpenedBy', 'OperatorID');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(BRUser::class, 'SupervisedBy', 'OperatorID');
    }

    public function contacts(): MorphMany
    {
        return $this->morphMany(Contact::class, 'party', "Party", "PartyID", 'ClientID');
    }

    public function crmmails(): MorphMany
    {
        return $this->morphMany(Email::class, 'party', "Party", "PartyID", 'ClientID');
    }

    public function crmsms(): MorphMany
    {
        return $this->morphMany(SMS::class, 'party', "Party", "PartyID", 'ClientID');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'party', "Party", "PartyID", 'ClientID');
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'ClientID');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'party', "Party", "PartyID", 'ClientID');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(SystemCodeDetail::class, 'ClientTypeID', 'SubCodeID')->where('ID', 'ClientTypeID');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(SystemCodeDetail::class, 'ClientStatusID', 'SubCodeID')->where('ID', 'ClientStatusID');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SystemCodeDetail::class, 'MemberClassID', 'SubCodeID')->where('ID', 'MemberClassID');
    }

    public function relation(): BelongsTo
    {
        return $this->belongsTo(SystemCodeDetail::class, 'RelationID', 'SubCodeID')->where('ID', 'RelationID');
    }

    public function corporate(): HasOne
    {
        return $this->hasOne(ClientCorporate::class, 'ClientID', 'ClientID');
    }

    public function individual(): HasOne
    {
        return $this->hasOne(ClientIndividual::class, 'ClientID', 'ClientID');
    }

    public function introducer(): BelongsToMany
    {
        return $this->belongsToMany(__CLASS__, table: 'syn_t_ClientIntroducer', foreignPivotKey: 'ClientID', relatedPivotKey: 'IntroducerClientID', parentKey: 'ClientID', relatedKey: 'ClientID');
    }

    public function relations(): BelongsToMany
    {
        return $this->belongsToMany(__CLASS__, table: 'syn_t_ClientRelation', foreignPivotKey: 'ClientID', relatedPivotKey: 'RelatedClientID', parentKey: 'ClientID', relatedKey: 'ClientID')->withPivot('RelationID');
    }

    public function getImage(string $attr = '', bool $placeholder = true): string
    {
        $photo = $this->photo;
        if ($photo instanceof ImageAccount) {
            return (new ImageService($photo))->get_image($attr, $placeholder);
        }

        return ($placeholder)
            ? '<img src="https://placehold.co/200x200?font=roboto&text=No+Image" ' . $attr . '/>'
            : '';
    }

    public function getSignature(string $attr = '', bool $placeholder = true): string
    {
        $photo = $this->signature;
        if ($photo instanceof ImageAccount) {
            return (new ImageService($this->signature))->get_image($attr, $placeholder);
        }

        return ($placeholder)
            ? '<img src="https://placehold.co/200x200?font=roboto&text=No+Signature" ' . $attr . '/>'
            : '';
    }

    //CRM Connections

    public function tickets(): MorphMany
    {
        return $this->morphMany(Ticket::class, 'party', "Party", "PartyID", 'ClientID');
    }

    public function calls(): MorphMany
    {
        return $this->morphMany(Call::class, 'party', "Party", "PartyID", 'ClientID');
    }

    public function meetings(): BelongsToMany
    {
        return $this->belongsToMany(Meeting::class, 't_MeetingClients', 'ClientID', 'MeetingId', 'ClientID', 'MeetingID')
            ->withTimestamps('CreatedOn', 'ModifiedOn')->withPivot(['CreatedBy', 'ModifiedBy'])->using(MeetingClient::class);
    }

    public function discussions(): MorphMany
    {
        return $this->morphMany(Discussion::class, 'party', "Party", "PartyID", 'ClientID');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Notes::class, 'party', "Party", "PartyID", 'ClientID');
    }

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class, 't_ScheduleClients', 'ClientID', 'ScheduleId', 'ClientID', 'ScheduleID')
            ->withTimestamps('CreatedOn', 'ModifiedOn')->withPivot(['CreatedBy', 'ModifiedBy']);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'party', "Party", "PartyID", 'ClientID');
    }

    public function marketingLists(): BelongsToMany
    {
        return $this->belongsToMany(MarketingList::class, 't_MarketingListParties', 'PartyID', 'MarketingListId', 'ClientID', 'MarketingListID')
            ->withTimestamps('CreatedOn', 'ModifiedOn')->withPivot(['CreatedBy', 'ModifiedBy'])->withPivotValue('t_MarketingListParties.Party', self::getPrimaryKey());
    }

    public static function getPrimaryKey(): string
    {
        return (new self())->primaryKey;
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
                'OpenedDate' => 'datetime',
                'CreatedOn' => 'datetime',
                'ModifiedOn' => 'datetime',
                'SupervisedOn' => 'datetime',
               ];
    }

    /* public function scopeWithWhereHas($query, $relation, $constraint){
         return $query->whereHas($relation, $constraint)
             ->with([$relation => $constraint]);
     }*/
}
