<?php

namespace App\Models\CRM;

use App\Models\Communication\Call;
use App\Models\Communication\Email;
use App\Models\Communication\SMS;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_Contacts';
    protected $primaryKey = 'ContactID';

    public static function getPrimaryKey(): string
    {
        return 'ContactId';
    }
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
                           'Label',
                           'Phone',
                           'Email',
                           'Party',
                           'PartyID',
                           'Extra',
                           'Notes',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    protected $casts = ['Extra' => 'object'];

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
        return $this->morphMany(Email::class, 'party', "Party", "PartyID", 'ContactID');
    }

    public function crmsms(): MorphMany
    {
        return $this->morphMany(SMS::class, 'party', "Party", "PartyID", 'ContactID');
    }
}
