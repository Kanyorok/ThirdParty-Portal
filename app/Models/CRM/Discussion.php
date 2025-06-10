<?php

namespace App\Models\CRM;

use App\Models\Auth\User;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discussion extends Pivot
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_Discussions';
    protected $primaryKey = 'DiscussionID';

    public static function getPrimaryKey(): string
    {
        return 'DiscussionID';
    }

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
