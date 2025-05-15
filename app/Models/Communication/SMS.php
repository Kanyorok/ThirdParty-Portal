<?php

namespace App\Models\Communication;

use App\Enums\EmailStatusEnum;
use App\Enums\EmailTypeEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SMS extends Model
{
    use UserActorTrait, SoftDeletes;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_SMS';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'SMSId', 'Phone', 'Type', 'Status', 'Content', 'Party', 'PartyID', 'Source', 'SourceID', 'Response', 'BulkNotificationId', 'Dated',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];


    protected $casts = [
        'Dated' => 'datetime',
        'Status' => EmailStatusEnum::class,
        'Type' => EmailTypeEnum::class,
        'Response' => 'object',
    ];

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'SMSId';
    }

    public function party(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Party", "PartyID");
    }

    public function source(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Source", "SourceID");
    }

    public function bulk(): BelongsTo
    {
        return $this->belongsTo(BulkNotification::class, 'BulkNotificationId', 'BulkNotificationID');
    }
}
