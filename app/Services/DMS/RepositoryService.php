<?php

namespace App\Services\DMS;

use App\Enums\Core\RoleEnum;
use App\Enums\Core\VisibilityEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\Core\SpecialPermission;
use App\Models\DMS\Repository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Throwable;

class RepositoryService extends PermissionsService
{
    protected const int ROOT = 1;

    public function __construct(public Repository $repo)
    {
    }

    public static function root(): Repository
    {
        return Repository::query()->where('Id', self::ROOT)->withTrashed()->firstOr(function () {
            $actor = SystemHelper::user();
            return self::_create(Name: 'Root', actor: $actor, Description: 'Root');
        });
    }

    /**
     * @throws ErroredException
     */
    private static function _create(string $Name, User $actor, Repository $repository = null, string $Description = ""): Repository
    {
        try {
            return DB::transaction(static function () use ($repository, $Name, $Description, $actor) {
                $visibility = (($repository instanceof Repository) && $repository->Visibility->value === VisibilityEnum::Private->value) ?
                    VisibilityEnum::Private : VisibilityEnum::Public;

                $repo = Repository::create([
                    'Name' => $Name,
                    'Description' => $Description,
                    'RepositoryId' => Uuid::uuid4()->toString(),
                    'ParentId' => $repository->Id ?? null,
                    'Visibility' => $visibility->value,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);

                //copy permissions
                if ($visibility->value === VisibilityEnum::Private->value) {
                    self::copyRepoPermissions($repository, $repo);
                }

                activity()->causedBy($actor)->performedOn($repo)->event('create')->log('Created folder : ' . $repo->Name);
                return $repo;
            });
        } catch (Exception|Throwable $e) {
            Log::error('Error creating repository: ');
            Log::error($e);
            throw new ErroredException();
        }
    }

    /**
     * @throws ErroredException
     */
    public static function create(Repository $repository, string $Name, User $actor, string $Description = ""): RepositoryService
    {
        return (new self(self::_create($Name, $actor, $repository, $Description)))
            ->addPermission($actor, RoleEnum::Admin, $actor, false);
    }

    /**
     * @throws ErroredException
     */
    public function update(string $Name, User $actor, string $Description): static
    {
        if ($this->isRoot()) {
            throw new ErroredException('Cannot update root folder');
        }
        try {
            return DB::transaction(function () use ($Name, $Description, $actor) {
                $this->repo->update([
                    'Name' => $Name,
                    'Description' => $Description,
                    'ModifiedBy' => $actor->Id,
                ]);

                activity()->causedBy($actor)->performedOn($this->repo)->event('update')->log('Updated folder name : ' . $this->repo->Name);
                return $this;
            });
        } catch (Exception|Throwable $e) {
            Log::error('Error update repository: ');
            Log::error($e);
            throw new ErroredException();
        }
    }

    public function parentRoot(): bool
    {
        return ($this->repo->ParentId === self::ROOT);
    }

    public function isRoot(): bool
    {
        return ($this->repo->Id === self::ROOT);
    }


    public function addPermission(User|Team $assignee, RoleEnum $role, User $actor, bool $notify = true): static
    {
        $this->_addPermissions($this->repo, $assignee, $role, $actor, $notify);
        return $this;
    }

    /**
     * @throws ErroredException
     */
    public function visibility(VisibilityEnum $visibility, User $actor): static
    {
        if ($this->isRoot()) {
            throw new ErroredException('Cannot update root folder');
        }
        try {
            return DB::transaction(function () use ($visibility, $actor) {
                $this->repo->update([
                    'Visibility' => $visibility->value,
                    'ModifiedBy' => $actor->Id,
                ]);

                activity()->causedBy($actor)->performedOn($this->repo)->event('update')->log('Updated folder ' . $this->repo->Name . ' visibility : ' . $visibility->value);
                return $this;
            });
        } catch (Exception|Throwable $e) {
            Log::error('Error update repository visibility: ');
            Log::error($e);
            throw new ErroredException();
        }
    }

    /**
     * @throws ErroredException
     */
    public function removePermission(SpecialPermission $permission, User $actor): static
    {
        $this->repo = $this->_trashPermissions($this->repo, $permission, $actor);
        return $this;
    }
}
