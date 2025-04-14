<?php

namespace App\Models;

use App\Models\BR\Client;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Board extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_BoardMembers';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
                           'BoardMemberID',
                           'Name',
                           'ClientID',
                           'Role',
                           'Phone',
                           'Email',
                           'Extra',
                           'Notes',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    protected $casts = ['Extra' => 'object'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'ClientID', 'ClientID');
    }

    public function crmmails(): MorphMany
    {
        return $this->morphMany(CrmEmail::class, 'party', "Party", "PartyID", 'ClientID');
    }

    public function crmsms(): MorphMany
    {
        return $this->morphMany(CrmSMS::class, 'party', "Party", "PartyID", 'ClientID');
    }

    public function committees(): BelongsToMany
    {
        return $this->belongsToMany(Committee::class, 't_BoardCommittee', 'BoardId', 'CommitteeId', 'Id', 'Id')
            ->withTimestamps('CreatedOn', 'ModifiedOn')->withPivot(['CreatedBy', 'ModifiedBy'])->using(BoardCommittee::class);
    }


    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'BoardMemberID';
    }

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }
}
