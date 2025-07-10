<?php

namespace App\Traits\Model;

use App\Enums\Core\VisibilityEnum;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\Core\SpecialPermission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait SpecialPermissionTrait
{
    public function permissions(): MorphMany
    {
        return $this->morphMany(SpecialPermission::class, 'model', "Model", "ModelID", 'Id');
    }

    public function scopeUser(Builder $q, User $user): Builder
    {
        return $q->where(function (Builder $query) use ($user) {
            $query->where($this->getTable() . '.Visibility', VisibilityEnum::Public->value)
                ->orWhereHas('permissions', function (Builder $q) use ($user) {
                    $q->where(function (Builder $q) use ($user) {
                        $q->where('Party', User::getPrimaryKey())
                            ->where('PartyID', $user->Id);
                    })->orWhere(function (Builder $q) use ($user) {
                        $q->where('Party', Team::getPrimaryKey())
                            ->whereIn('PartyID', $user->teams()->select('t_Teams.TeamID'));
                    });
                });
        });
    }

    public function getSharedName()
    {
        return $this->Name;
    }

    /**
     * #permission -> role / permission name eg Read, Write ...
     */
    abstract public function getShareEmailSubject(): string;
}
