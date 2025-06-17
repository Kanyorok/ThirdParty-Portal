<?php

namespace App\Services\DMS;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\VisibilityEnum;
use App\Enums\DMS\DisksEnum;
use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Core\CategoryMaster;
use App\Models\DMS\Document;
use App\Models\DMS\Repository;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $path = $disk->path() . '/' . $file->getFilename();
        $checksum = hash_file('sha256', $path);

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

}
