<?php

namespace App\Models;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discussion extends Pivot
{
    use UserActorTrait;
    use SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_Discussions';
    protected $primaryKey = 'DiscussionID';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
                           "Party",
                           "PartyID",
                           'SourceType',
                           'SourceTypeID',
                           'Discussion',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    // (Call, Meeting)
    public function source(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'SourceType', 'SourceTypeID');
    }

    //Client/Lead
    public function party(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Party", "PartyID");
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 't_DiscussionsUsers', 'DiscussionId', 'UserID')
            ->withPivot(['CreatedBy', 'ModifiedBy'])->withTimestamps()->withTrashed();
        //->using(DiscussionUser::class);
    }

    public function discussionUser(): HasMany
    {
        return $this->hasMany(DiscussionUser::class, 'DiscussionId', 'DiscussionID');
    }
}
