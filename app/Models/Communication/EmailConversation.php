<?php

namespace App\Models\Communication;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailConversation extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_EmailsConversations';
    protected $primaryKey = 'Id';

    protected $fillable = [
                           'Emails',
                           'EmailId',
                           'Party',
                           'PartyID',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    protected $casts = [
                        'EmailId' => 'integer',
                        'Emails'  => 'integer',
                       ];

    public function party(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Party", "PartyID");
    }

    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class, 'EmailId', 'EmailID');
    }

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class, 'EmailConversationId', 'Id');
    }

    public function watchers(): HasMany
    {
        return $this->hasMany(EmailConversationUser::class, 'EmailConversationId', 'Id');
    }
}
