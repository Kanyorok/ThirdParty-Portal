<?php

namespace App\Models\Core;

use App\Models\Auth\User;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_PartyActivities';
    protected $primaryKey = 'ActivityID';

    public static function getPrimaryKey(): string
    {
        return 'ActivityID';
    }

    protected $fillable = [
                           "Party",
                           "PartyID",
                           'UserID',
                           'Notes',
                           'ActivityType',
                           'ActivityTypeID',
                           'CreatedOn',
                           'CreatedBy',
                           'ModifiedOn',
                           'ModifiedBy',
                          ];

    // (Call, Schedule, )
    public function activityType(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'ActivityType', 'ActivityTypeID');
    }

    //Client, Leads
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
