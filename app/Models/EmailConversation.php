<?php

namespace App\Models;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailConversation extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_CRMEmailsConversations';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Emails', 'EmailId', 'Party', 'PartyID',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    protected $casts = [
        'EmailId' => 'integer',
        'Emails' => 'integer'
    ];

    public function party(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Party", "PartyID");
    }

    public function email(): BelongsTo
    {
        return $this->belongsTo(CrmEmail::class, 'EmailId', 'EmailID');
    }

    public function emails(): HasMany
    {
        return $this->hasMany(CrmEmail::class, 'EmailConversationId', 'Id');
    }

    public function watchers(): HasMany
    {
        return $this->hasMany(EmailConversationUser::class, 'EmailConversationId', 'Id');
    }
}
