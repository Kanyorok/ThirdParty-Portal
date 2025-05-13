<?php

namespace App\Traits\Model;

use App\Enums\Core\ExtensionsEnum;
use App\Models\CRMImage;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use RuntimeException;

trait ImageTrait
{
    public function photo(): BelongsTo
    {
        return $this->belongsTo(CRMImage::class, 'ImageId', 'ImageID');
    }

    public function getImage(string $attr = '', bool $placeholder = true, string $ImageRelationFn = 'photo'): string
    {
        if (!method_exists($this, $ImageRelationFn)) {
            throw new RuntimeException('Could not find the image');
        }

        $photo = $this->$ImageRelationFn;
        if ($photo instanceof CRMImage) {
            $service = new ImageService($photo);
            if ($service->isPrevieable()) {
                return $service->preview($attr);
            }
        }

        return ($placeholder) ? '<img src="https://placehold.co/200x200?font=roboto&text=No+Image" ' . $attr . '/>' : '';
    }

    public function setImage(UploadedFile $file, User $actor, string $field = null): static
    {
        if (is_string($field) && !in_array($field, $this->fillable, true)) {
            throw new RuntimeException('Invalid field');
        }

        return $this->_setImage(ImageService::createUpload($file, $this->primaryKey, $this->{$this->primaryKey}, $actor), $field);
    }

    public function setFromContent(string $content, User $actor, string $mimeType, string $fileName, string $field = null): static
    {
        if (is_string($field) && !in_array($field, $this->fillable, true)) {
            throw new RuntimeException('Invalid field');
        }

        return $this->_setImage(ImageService::createContent($content, $this->primaryKey, $this->{$this->primaryKey}, $mimeType, $fileName, $actor), $field);
    }


    protected function _setImage(ImageService $service, string $field = null): static
    {
        if (is_string($field)) {
            $this->update([
                $field => $service->image->ImageID,
            ]);
        }
        return $this;
    }

    public function setAvatarFromURL(string $url, User $actor, string $field = null): static
    {
        if (is_string($field) && !in_array($field, $this->fillable, true)) {
            throw new RuntimeException('Invalid field');
        }

        return $this->_setImage(ImageService::createURL($url, $this->primaryKey, $this->{$this->primaryKey}, $actor, ExtensionsEnum::Jpeg->getMimeType()), $field);
    }
}
