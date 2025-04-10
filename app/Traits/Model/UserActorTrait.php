<?php

namespace App\Traits\Model;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\Activitylog\Exceptions\InvalidConfiguration;

trait UserActorTrait
{
    /* public function getMorphClass()
     {
         return $this->primaryKey;
     }*/

    public static function getPrimaryKey(): string
    {
        return (new self)->primaryKey;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id')->withTrashed();
    }

    public function modified(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id')->withTrashed();
    }

    public function deleter(): ?BelongsTo
    {
        return (in_array("DeletedBy", $this->fillable, true))
            ? $this->belongsTo(User::class, 'DeletedOn', 'Id')->withTrashed() : null;
    }

    /**
     * @throws InvalidConfiguration
     */
    public function userActivities(): MorphMany
    {
        return $this->morphMany(ActivitylogServiceProvider::determineActivityModel(), 'subject');
    }

}
