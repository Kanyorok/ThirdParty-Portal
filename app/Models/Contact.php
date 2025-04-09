<?php

namespace App\Models;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_Contacts';
    protected $primaryKey = 'ContactID';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'Label', 'Phone', 'Email', 'Party', 'PartyID', 'Extra',
        'Notes', 'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    protected $casts = [
        'Extra' => 'object',
    ];

    public function party(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Party", "PartyID");
    }

    public function calls(): MorphMany
    {
        return $this->morphMany(Call::class, 'party', "Party", "PartyID", 'ContactID');
    }

    public function crmmails(): MorphMany
    {
        return $this->morphMany(CrmEmail::class, 'party', "Party", "PartyID", 'ContactID');
    }

    public function crmsms(): MorphMany
    {
        return $this->morphMany(CrmSMS::class, 'party', "Party", "PartyID", 'ContactID');
    }
}
