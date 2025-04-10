<?php

namespace App\Services;

use App\Enums\Core\ExtensionsEnum;
use App\Exceptions\ErroredException;
use App\Models\CRMImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImageService
{
    public ExtensionsEnum $type;

    public function __construct(public CRMImage $image)
    {
        $this->setType();
    }

    private function setType(): void
    {
        try {
            $type = ExtensionsEnum::fromMimeType($this->image->MIMEType);
        } catch (ErroredException $e) {
            $type = ExtensionsEnum::None;
        }
        $this->type = $type;
    }


    public static function createUpload(UploadedFile $file, string $Type, string $TypeID, User $actor): self
    {
        return self::create($Type, $TypeID, $file->getContent(), $file->getMimeType() ?? $file->getClientMimeType(), $file->getClientOriginalName(), $actor);
    }

    public static function createContent(string $fileContent, string $Type, string $TypeID, string $MimeType, string $fileName, User $actor): self
    {
        return self::create($Type, $TypeID, $fileContent, $MimeType, $fileName, $actor);
    }

    public static function createURL(string $url, string $Type, string $TypeID, User $actor, string $MimeType): self
    {
        return self::create($Type, $TypeID, file_get_contents($url), $MimeType, explode('?', basename($url))[0], $actor);
    }

    public static function create(string $Type, string $TypeID, string $Content, string $MimeType, string $Name, User $actor): self
    {
        $img = new CRMImage();
        $img->fill([
            "Name" => $Name,
            "ImageType" => $Type,
            "ImageTypeID" => $TypeID,
            "Image" => base64_encode($Content),
            "MIMEType" => $MimeType,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ])->save();

        return new self($img->refresh());
    }

    public function isPrevieable(): bool
    {
        return $this->type->isPreview();
    }

    public function preview(string $attr): string
    {
        if (!$this->type->isPreview()) {
            return '';
        }

        if ($this->type->isImage()) {
            return '<img src="data:' . $this->image->MIMEType . ';base64,' . $this->image->Image . '" ' . $attr . ' >';
        }

        if ($this->type->isVideo()) {
            return '<video controls src="data:' . $this->image->MIMEType . ';base64,' . $this->image->Image . '" ' . $attr . '>Sorry, your browser doesn\'t support embedded videos</video>';
        }

        if ($this->type->value === ExtensionsEnum::Pdf->value) {
            return '<iframe src="data:application/pdf;base64,' . $this->image->Image . '" ' . $attr . '></iframe>';
            //return '<embed width="100%" height="100%" "data:application/pdf;base64,'.$this->image->Image.' type="application/pdf" />';
        }

        return '';
    }

    public function summaryList(): string
    {
        return '<span class="btn btn-outline-info modal-preview-document" title="' . $this->image->Name . '"
                        data-url="' . route('documents.show', [$this->image->ImageID]) . '" id="document-' . $this->image->ImageID . '">
                    ' . $this->image->ext()?->getIcon() . "&nbsp;" . Str::limit(explode(".", $this->image->Name)[0], 10) . '.' . $this->image->ext()?->value . '</span>';
    }

}
