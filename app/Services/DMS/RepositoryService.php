<?php

namespace App\Services\DMS;

use App\Enums\Core\VisibilityEnum;
use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\DMS\Repository;

class RepositoryService extends PermissionsService
{
    protected const int ROOT = 1;

    public function __construct(public Repository $repo)
    {
    }

    public static function root()
    {
        return Repository::query()->where('Id', self::ROOT)->withTrashed()->firstOr(function () {
            $actor = SystemHelper::user();
            return self::_create(Name: 'Root', actor: $actor, Description: 'Root');
        });
    }

    private static function _create(string $Name, User $actor, Repository $repository = null, string $Description = ""): Repository
    {
        $visibility = (($repository instanceof Repository) && $repository->Visibility->value === VisibilityEnum::Private->value) ?
            VisibilityEnum::Private : VisibilityEnum::Public;

        $repo = Repository::create([
            'Name' => $Name,
            'Description' => $Description,
            'RepositoryID' => $repository->Id ?? null,
            'Visibility' => $visibility->value,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        //copy permissions
        if ($visibility->value === VisibilityEnum::Private->value) {
            self::copyRepoPermissions($repository, $repo);
        }

        return $repo;
    }

    public static function create(Repository $repository, string $Name, User $actor, string $Description = ""): RepositoryService
    {
        return new self(self::_create($Name, $actor, $repository, $Description));
    }

    public function isRoot(): bool
    {
        return ($this->repo->Id === self::ROOT);
    }
}
