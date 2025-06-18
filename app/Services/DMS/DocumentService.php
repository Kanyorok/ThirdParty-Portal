<?php

namespace App\Services\DMS;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\VisibilityEnum;
use App\Enums\DMS\DisksEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\Core\CategoryMaster;
use App\Models\DMS\DMSTags;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentTags;
use App\Models\DMS\Repository;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Ramsey\Uuid\Uuid;
use Storage;
use Throwable;

class DocumentService extends PermissionsService
{
    public function __construct(public Document $document)
    {
    }

    /**
     * @throws ErroredException
     */
    public static function createUpload(Repository $repository, UploadedFile $file, User $actor): self
    {
        $extension = ExtensionsEnum::fromMimeType($file->getMimeType() ?? $file->getClientMimeType());
        $disk = DisksEnum::Local;
        $path = $disk->path() . '/' . Uuid::uuid4()->toString() . '.' . $extension->value;
        $checksum = hash_file('sha256', $file->getRealPath());

        if (Storage::disk($disk->value)->put($path, $file->getContent())) {//for blob use https://github.com/NilGems/laravel-textract
            return self::_create($repository, $actor, $disk, $file->getClientOriginalName(), $extension, $path, $file->getSize(), $checksum, '');
        }

        throw new ErroredException('Saving file failed.');
    }

    /**
     * @throws ErroredException
     */
    private static function _create(Repository $repository, User $actor, DisksEnum $disk, string $name, ExtensionsEnum $extension, string $path, int $sizeInBytes, string $checksum, string $blob, CategoryMaster|null $category = null): DocumentService
    {
        try {
            return DB::transaction(static function () use ($blob, $sizeInBytes, $checksum, $disk, $path, $category, $extension, $repository, $name, $actor) {
                $document = Document::create([
                    "Name" => $name,
                    "MimeType" => $extension->getMimeType(),
                    "CategoryId" => $category?->Id,
                    'DocumentId' => Uuid::uuid4()->toString(),
                    "RepositoryId" => $repository->Id,
                    "Visibility" => $repository->Visibility->value,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);

                if ($repository->Visibility->value === VisibilityEnum::Private->value) {
                    self::copyRepoPermissions($repository, $document);
                }

                $document->versions()->create([
                    "Name" => $name,
                    "Version" => 1,
                    "Path" => $path,
                    "Disk" => $disk->value,
                    "Checksum" => $checksum,
                    "Size" => $sizeInBytes,
                    "Blob" => $blob,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);

                activity()->causedBy($actor)->performedOn($document)->event('upload')->log('Uploaded ' . explode($extension->getMimeType(), '/')[0] . ' to folder ' . $repository->Name);

                return new self($document);
            });
        } catch (Exception|Throwable $e) {
            Log::error('Error creating repository: ' . $e->getMessage());
            throw new ErroredException('Saving file failed.');
        }
    }

    public function html(): string
    {
        return '<tr> <td> <div class="d-flex align-items-center"><img src="' . $this->document->ext()?->getIcon('img') . '" alt="ICO" class="wid-35">
                <h6 class="mb-0 ms-2 text-truncate">' . $this->document->Name . '</h6> </div> </td> <td>' . Number::fileSize($this->document->current->Size, 2) . '</td> <td>' . $this->document->current->ModifiedOn->format('d M Y H:i') . '</td>
                <td> <div class="user-group p-1">' . $this->_usersHtml() . '</div> </td>
                <td> <div class="d-flex flex-wrap gap-2"> ' . $this->_tagsHtml() . ' </div> </td> <td> <ul class="list-inline text-end">
                <li class="list-inline-item"> ' . $this->_permissionHtml() . ' </li>
                <li class="list-inline-item"> <a href="#" class="btn btn-outline-info btn-sm"> <i data-feather="eye" class="text-info"></i> details</a> </li> </ul> </td> </tr>';
    }

    private function _permissionHtml(): string
    {
        return ($this->document->Visibility->value === VisibilityEnum::Private->value)
            ? '<i data-feather="lock" title="Private" class="text-danger icon-size"></i>'
            : '<i data-feather="globe" title="Public" class="text-primary icon-size"></i> ';

    }

    private function _tagsHtml(): string
    {
        /*return  $this->document->tags()->paginate(5)->map(function ($tag) {
            return ($tag->Visibility->value === VisibilityEnum::Private->value)
                ? '<span class="badge rounded-pill text-bg-danger">'.$tag->Name.'</span>'
                : '<span class="badge rounded-pill text-bg-primary">'.$tag->Name.'</span>';
        });*/
        return '';
    }

    private function _usersHtml(): string
    {
        if ($this->document->Visibility->value === VisibilityEnum::Public->value) {
            return $this->document->creator?->getImage('alt="user-image" class="avtar"') . '<span class="avtar avtar-xs bg-light-primary text-primary">+' . User::count() . '</span>';
        }

        $users = User::query()->where(function (Builder $query) {
            $query->where(function (Builder $query) {
                $query->whereIn('t_Users.Id', $this->document->permissions()->where('Party', User::getPrimaryKey())->select('PartyID'));
            })->orWhereHas('teams', function (Builder $query) {
                $query->whereIn('t_Teams.TeamID', $this->document->permissions()->where('Party', Team::getPrimaryKey())->select('PartyID'));
            });
        })->paginate(5);
        dd($users);


        /* <img src="../assets/images/user/avatar-1.jpg" alt="user-image" class="avtar">
         <img src="../assets/images/user/avatar-2.jpg" alt="user-image" class="avtar">
         <img src="../assets/images/user/avatar-3.jpg" alt="user-image" class="avtar">
         <span class="avtar avtar-xs bg-light-primary text-primary">+2</span>*/
    }

    public function users(): Builder
    {
        if ($this->document->Visibility->value === VisibilityEnum::Public->value) {
            return User::query()->where('UserID', '!=', SystemHelper::ID);
        }

        return User::query()->where('UserID', '!=', SystemHelper::ID)->where(function (Builder $query) {
            $query->where(function (Builder $query) {
                $query->whereIn('t_Users.Id', $this->document->permissions()->where('Party', User::getPrimaryKey())->select('PartyID'));
            })->orWhereHas('teams', function (Builder $query) {
                $query->whereIn('t_Teams.TeamID', $this->document->permissions()->where('Party', Team::getPrimaryKey())->select('PartyID'));
            });
        });

    }

}
