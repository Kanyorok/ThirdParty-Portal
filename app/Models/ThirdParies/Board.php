<?php

namespace App\Models\ThirdParies;

use App\Models\BR\Client;
use App\Models\Communication\Email;
use App\Models\Communication\SMS;
use App\Models\HRM\Committee;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Board extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_BoardMembers';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'BoardMemberID', 'Name', 'ClientID', 'Role', 'Phone', 'Email', 'Extra', 'Notes', 'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = ['Extra' => 'object'];

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'BoardMemberID';
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'ClientID', 'ClientID');
    }

    public function crmmails(): MorphMany
    {
        return $this->morphMany(Email::class, 'party', "Party", "PartyID", 'ClientID');
    }

    public function crmsms(): MorphMany
    {
        return $this->morphMany(SMS::class, 'party', "Party", "PartyID", 'ClientID');
    }

    public function committees(): BelongsToMany
    {
        return $this->belongsToMany(Committee::class, 't_BoardCommittee', 'BoardId', 'CommitteeId', 'Id', 'Id')
            ->withTimestamps('CreatedOn', 'ModifiedOn')->withPivot(['CreatedBy', 'ModifiedBy'])->using(BoardCommittee::class);
    }
    public static function getScheduleType(): string
{
    return 'BoardID';
}

}
