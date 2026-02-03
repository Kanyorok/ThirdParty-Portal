<?php

namespace App\Models\CRM;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notes extends Model
{
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';

    protected $table = 't_Notes';
    protected $primaryKey = 'NoteID';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'DiscussionID', "Party", "PartyID", 'Notes', 'CreatedOn', 'CreatedBy', 'ModifiedOn', 'ModifiedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'NoteID';
    }

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class, 'DiscussionID', 'DiscussionID');
    }

    public function party(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Party", "PartyID");
    }
}
