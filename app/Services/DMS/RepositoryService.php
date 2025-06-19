<?php

namespace App\Services\DMS;

use App\Enums\Core\VisibilityEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\Auth\User;
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

    public static function root()
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
        return new self(self::_create($Name, $actor, $repository, $Description));
    }

    public function parentRoot(): bool
    {
        return ($this->repo->ParentId === self::ROOT);
    }

    public function isRoot(): bool
    {
        return ($this->repo->Id === self::ROOT);
    }
}
