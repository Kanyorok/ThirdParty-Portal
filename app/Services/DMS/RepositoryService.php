<?php

namespace App\Services\DMS;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\RoleEnum;
use App\Enums\Core\VisibilityEnum;
use App\Enums\DMS\DocumentValidationTypeEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\Core\SpecialPermission;
use App\Models\DMS\Repository;
use App\Services\Core\PermissionsService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Throwable;

class RepositoryService extends PermissionsService
{
    protected const string ROOT = 'root';
    protected const string Internal = 'internal';
    protected const string Validation = 'validation';

    public function __construct(public Repository $repo)
    {
    }

    public static function getUser(User $actor, array $parents = []): Collection
    {
        return self::getUserQuery($actor, $parents)->get();
    }

    public static function getUserQuery(User $actor, array $parents = []): Builder
    {
        $query = Repository::query();
        if (!empty($parents)) {
            $query->where(function (Builder $query) use ($parents) {
                if (in_array(null, $parents, true)) {
                    $query->whereNull('ParentId');
                }
                $parents = array_filter($parents, static function ($var) {
                    return $var !== null;
                });
                if (!empty($parents)) {
                    $query->orWhereIn('ParentId', $parents);
                }
            });
        }

        return $query->user($actor);
    }

    public static function module(ModulesEnum $module): Repository
    {
        return Repository::query()->where('RepositoryId', (string)$module->value)->withTrashed()->firstOr(function () use ($module) {
            $actor = SystemHelper::user();
            return (new self(self::_create(Name: $module->description(), actor: $actor, repository: self::internal(), Description: $module->description() . ' Uploaded files', RepoId: $module->value)))
                ->visibility(VisibilityEnum::Private, $actor)->repo;
        });
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

    public function isRoot(): bool
    {
        return ($this->repo->RepositoryId === self::ROOT);
    }

    public function getPath(): string
    {
        if ($this->isRoot()) {
            return '/';
        }

        try {
            $path = collect(DB::select(
                "SELECT RepositoryId, Name FROM f_parent_repositories(?) WHERE RepositoryId <> 'root' ORDER BY ParentId",
                [$this->repo->Id]));

            return '/' . $path->implode(function ($item) {
                    return $item->Name;
                }, '/');
        } catch (Exception|Throwable $e) {
            return '/??';
        }
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

    /**
     * @throws ErroredException
     */
    private static function _create(string $Name, User $actor, Repository $repository = null, string $Description = "", string $RepoId = null): Repository
    {
        if (is_null($repository) && $RepoId !== self::ROOT) {
            throw new ErroredException('Please provide a repository to create a folder under');
        }
        try {
            return DB::transaction(static function () use ($repository, $Name, $Description, $actor, $RepoId) {
                $visibility = (($repository instanceof Repository) && $repository->Visibility->value === VisibilityEnum::Private->value) ?
                    VisibilityEnum::Private : VisibilityEnum::Public;

                $repo = Repository::create([
                    'Name' => $Name,
                    'Description' => $Description,
                    'RepositoryId' => $RepoId ?? Uuid::uuid4()->toString(),
                    'ParentId' => $repository->Id ?? null,
                    'Visibility' => $visibility->value,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);

                //copy permissions
                if ($visibility->value === VisibilityEnum::Private->value) {
                    self::copyPermissions($repository, $repo, $actor);
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
            ->addPermission($actor, RoleEnum::Admin, SystemHelper::user(), false);
    }

    /**
     * @throws ErroredException
     */
    public function addPermission(User|Team $assignee, RoleEnum $role, User $actor, bool $notify = true): static
    {
        $this->_addPermissions($this->repo, $assignee, $role, $actor, $notify);
        return $this;
    }

    /**
     * Internal Repository
     * @throws ErroredException
     */
    public static function internal(): Repository
    {
        return Repository::query()->where('RepositoryId', self::Internal)->withTrashed()->firstOr(function () {
            $actor = SystemHelper::user();
            return (new self(self::_create(Name: 'Internal', actor: $actor, repository: self::root(), Description: 'Internal Uploaded', RepoId: self::Internal)))
                ->visibility(VisibilityEnum::Private, $actor)->repo;
        });
    }

    public static function validation(DocumentValidationTypeEnum $validationType = null): Repository
    {
        if ($validationType === null) {
            return Repository::query()->where('RepositoryId', self::Validation)->withTrashed()->firstOr(function () {
                $actor = SystemHelper::user();
                return (new self(self::_create(Name: 'Document Validation', actor: $actor, repository: self::root(), Description: 'Document Validation', RepoId: self::Validation)))
                    ->visibility(VisibilityEnum::Public, $actor)->repo;
            });
        }

        return Repository::query()->where('RepositoryId', $validationType->value)->withTrashed()->firstOr(function () use ($validationType) {
            $actor = SystemHelper::user();
            return (new self(self::_create(Name: $validationType->description(), actor: $actor, repository: self::validation(), Description: $validationType->description() . ' Uploaded files', RepoId: $validationType->value)))
                ->visibility(VisibilityEnum::Public, $actor)->repo;
        });


    }

    public static function root(): Repository
    {
        return Repository::query()->where('RepositoryId', self::ROOT)->withTrashed()->firstOr(function () {

            return self::_create(Name: 'Root', actor: SystemHelper::user(), Description: 'Root', RepoId: self::ROOT);
        });
    }

    public function parentRoot(): bool
    {
        return ($this->repo->ParentId === self::root()->Id);
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
