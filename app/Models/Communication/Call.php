<?php

namespace App\Models\Communication;

use App\Enums\CallStatusEnum;
use App\Enums\CallTypeEnum;
use App\Models\Auth\User;
use App\Models\CRM\Discussion;
use App\Models\CRM\Schedule;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Call extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_Calls';
    protected $primaryKey = 'CallID';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
                           'ScheduleID',
                           "Party",
                           "PartyID",
                           'UserID',
                           'StartOn',
                           'EndOn',
                           'CallStatusID',
                           'CallTypeID',
                           'Source',
                           'SourceID',
                           'Notes',
                           'Response',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    protected $casts = [
                        'StartOn'      => 'datetime',
                        'EndOn'        => 'datetime',
                        'CallStatusID' => CallStatusEnum::class,
                        'CallTypeID'   => CallTypeEnum::class,
                        'Response'     => 'object',
                       ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'ScheduleID', 'ScheduleID');
    }

    public function party(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Party", "PartyID");
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserID', 'Id');
    }

    public function discussion(): MorphOne
    {
        return $this->morphOne(Discussion::class, 'source', 'SourceType', 'SourceTypeID', 'CallID');
    }

    public function source(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Source", "SourceID");
    }
}
