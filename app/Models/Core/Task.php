<?php

namespace App\Models\Core;

use App\Models\Auth\User;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_Tasks';
    protected $primaryKey = 'TaskID';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
                           "Party",
                           "PartyID",
                           "UserID",
                           "Dated",
                           "CompletedOn",
                           "Notes",
                           'Source',
                           'SourceID',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    protected $casts = [
                        'Dated'       => 'datetime',
                        'CompletedOn' => 'datetime',
                        'UserID'      => 'integer',
                        'CreatedBy'   => 'integer',
                        'ModifiedBy'  => 'integer',
                       ];

    public function party(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Party", "PartyID");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserID', 'Id');
    }

    public function source(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Source", "SourceID");
    }
}
