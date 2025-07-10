<?php

namespace App\Services\DMS;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\ModulesEnum;
use App\Enums\Core\RoleEnum;
use App\Enums\Core\VisibilityEnum;
use App\Enums\DMS\DisksEnum;
use App\Events\DMS\DocumentCreatedEvent;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\Core\CategoryMaster;
use App\Models\Core\SpecialPermission;
use App\Models\DMS\Document;
use App\Models\DMS\Repository;
use App\Services\Core\PermissionsService;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Ramsey\Uuid\Uuid;
use Storage;
use Throwable;

class DocumentService extends PermissionsService
{
    public ExtensionsEnum $type;

    public function __construct(public Document $document)
    {
        $this->setType();
    }

    private function setType(): void
    {
        try {
            $type = ExtensionsEnum::fromMimeType($this->document->MimeType);
        } catch (ErroredException $e) {
            $type = ExtensionsEnum::None;
        }
        $this->type = $type;
    }

    /**
     * @throws ErroredException
     */
    public static function createInternal(ModulesEnum $module, UploadedFile $file, User $actor, array|string $permissions, string $Related, string|int $RelatedId): self
    {
        $service = self::createUpload(RepositoryService::module($module), $file, $actor, false)
            ->addPermission($actor, RoleEnum::Admin, $actor, false)->attach($Related, $RelatedId, $actor);

        if (empty($permissions)) {
            if (is_string($permissions)) {
                $permissions = explode(',', $permissions);
            }
            self::userPermissions($service->document, $permissions, RoleEnum::Read, $actor);
        }
        return $service;
    }

    /**
     * @throws ErroredException
     */
    public function attach(string $Related, string|int $RelatedId, User $actor): self
    {
        if (is_null(Relation::getMorphedModel($Related))) {
            throw new ErroredException('Invalid Related Entity');
        }

        $this->document->relations()->create([
            'Related' => $Related,
            'RelatedId' => $RelatedId,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        activity()->causedBy($actor)->performedOn($this->document)->event('upload')->log('relation added');

        return $this;
    }

    /**
     * @throws ErroredException
     */
    public static function createUpload(Repository $repository, UploadedFile $file, User $actor, bool $copyRepoPermissions = true): self
    {
        $extension = ExtensionsEnum::fromMimeType($file->getMimeType() ?? $file->getClientMimeType());
        $disk = DisksEnum::Local;

        $checksum1 = hash_file('sha256', $file->getRealPath());

        $properties = (new FileProperties($file, $extension))->getProperties();
        $path = self::_saveFile($disk, $file->getContent());
        $checksum2 = hash_file('sha256', Storage::disk($disk->value)->path($path));
        $checksum = base64_encode($checksum1 . '|' . $checksum2);

        return self::_create($repository, $actor, $disk, $file->getClientOriginalName(), $extension, $path, $file->getSize(), $checksum, '', properties: $properties, copyPermissions: $copyRepoPermissions = false);
    }

    /**
     * @throws ErroredException
     */
    private static function _saveFile(DisksEnum $disk, string $contents): string
    {
        // $path = $disk->path() . '/' . Uuid::uuid4()->toString() . '.' . $extension->value;
        $path = $disk->path() . '/' . Uuid::uuid4()->toString() . '.data';
        if (Storage::disk($disk->value)->put($path, (new EncryptionService())->encrypt($contents))) {
            return $path;
        }
        throw new ErroredException('Saving file failed.');

    }

    public function getFileContent(bool $base64 = true): string
    {
        $currentVersion = $this->document->current;
        $content = (new EncryptionService())->decrypt(Storage::disk($currentVersion->Disk->value)->get($currentVersion->Path));
        return ($base64) ? base64_encode($content) : $content;
    }

    public function isPrevieable(): bool
    {
        return $this->type->isPreview();
    }

    public function preview(string $attr): string
    {
        if (!$this->isPrevieable()) {
            return '';
        }

        if ($this->type->isImage()) {
            return '<img src="data:' . $this->document->MIMEType . ';base64,' . $this->getFileContent() . '" ' . $attr . ' >';
        }

        if ($this->type->isVideo()) {
            return '<video controls src="data:' . $this->document->MIMEType . ';base64,' . $this->getFileContent() . '" ' . $attr . '>Sorry, your browser doesn\'t support embedded videos</video>';
        }

        if ($this->type->value === ExtensionsEnum::Pdf->value) {
            return '<iframe src="data:application/pdf;base64,' . $this->getFileContent() . '" ' . $attr . '></iframe>';
            //return '<embed width="100%" height="100%" "data:application/pdf;base64,'.$this->image->Image.' type="application/pdf" />';
        }


        if ($this->type->value === ExtensionsEnum::Txt->value) {
            return '<textarea readonly disabled ' . $attr . '>' . $this->getFileContent(false) . '</textarea>';
        }
        return '';
    }

    /**
     * @throws ErroredException
     */
    private static function _create(
        Repository $repository, User $actor, DisksEnum $disk, string $name, ExtensionsEnum $extension, string $path, int $sizeInBytes, string $checksum, string $blob, Collection $properties,
        CategoryMaster|null $category = null, bool $copyPermissions = true): DocumentService
    {
        try {
            return DB::transaction(static function () use ($properties, $blob, $sizeInBytes, $checksum, $disk, $path, $category, $extension, $repository, $name, $actor, $copyPermissions) {
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

                if ($copyPermissions && $repository->Visibility->value === VisibilityEnum::Private->value) {
                    self::copyPermissions($repository, $document, $actor);
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

                $date = now();
                $properties = $properties->map(function ($property) use ($actor, $date, $document) {
                    $value = $property['Value'] ?? '';

                    // Convert value to string to avoid type conversion issues
                    if (is_bool($value)) {
                        $value = $value ? '1' : '0';
                    } elseif (is_numeric($value)) {
                        $value = (string)$value;
                    } elseif ($value instanceof DateTime) {
                        $value = $value->format('Y-m-d H:i:s');
                    } else {
                        $value = (string)$value;
                    }

                    return [
                        'DocumentId' => (int)$document->Id,
                        'CreatedBy' => (int)$actor->Id,
                        'ModifiedBy' => (int)$actor->Id,
                        'CreatedOn' => $date,
                        'ModifiedOn' => $date,
                        'DataType' => $property['DataType'] ?? 'st',
                        'Name' => $property['Name'] ?? '',
                        'Value' => $value
                    ];
                });

                if ($properties->isNotEmpty()) {
                    DB::table('t_DocumentAttributes')->insert($properties->toArray());
                }

                activity()->causedBy($actor)->performedOn($document)->event('upload')->log('Uploaded ' . explode($extension->getMimeType(), '/')[0] . ' to folder ' . $repository->Name);

                event(new DocumentCreatedEvent($document));

                $service = new self($document);
                if ($repository->Visibility->value === VisibilityEnum::Public->value) {
                    return $service->addPermission($actor, RoleEnum::Admin, $actor, false);
                }
                return $service;
            });
        } catch (Exception|Throwable $e) {
            Log::error('Error creating document: ');
            Log::error($e);
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

    public function tags(User $user): BelongsToMany
    {
        return $this->document->tags()->where(function (Builder $query) use ($user) {
            $query->where('Visibility', VisibilityEnum::Public->value)
                ->orWhere(function (Builder $query) use ($user) {
                    $query->where('Visibility', VisibilityEnum::Private->value)
                        ->where('t_DMSTags.CreatedBy', $user->Id);
                });
        });
    }

    private function _tagsHtml(): string
    {
        /*return  ->paginate(5)->map(function ($tag) {
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

    public function addPermission(User|Team $assignee, RoleEnum $role, User $actor, bool $notify = true): static
    {
        $this->_addPermissions($this->document, $assignee, $role, $actor, $notify);
        return $this;
    }

    /**
     * @throws ErroredException
     */
    public function visibility(VisibilityEnum $visibility, User $actor): static
    {
        try {
            return DB::transaction(function () use ($visibility, $actor) {
                $this->document->update([
                    'Visibility' => $visibility->value,
                    'ModifiedBy' => $actor->Id,
                ]);

                activity()->causedBy($actor)->performedOn($this->document)->event('update')->log('Updated file ' . $this->document->Name . ' visibility : ' . $visibility->description());
                return $this;
            });
        } catch (Exception|Throwable $e) {
            Log::error('Error update document visibility: ');
            Log::error($e);
            throw new ErroredException();
        }
    }

    /**
     * @throws ErroredException
     */
    public function removePermission(SpecialPermission $permission, User $actor): static
    {
        $this->document = $this->_trashPermissions($this->document, $permission, $actor);
        return $this;
    }

}
