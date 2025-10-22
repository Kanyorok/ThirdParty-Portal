<?php

namespace App\Services\DMS\Verification;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\VisibilityEnum;
use App\Enums\DMS\ImageGravityEnum;
use App\Exceptions\ErroredException;
use App\Jobs\SignDocumentJob;
use App\Models\Auth\User;
use App\Models\DMS\DMSSignature;
use App\Models\DMS\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class SignatureService extends SignService
{
    public function __construct(public DMSSignature $signature)
    {
    }

    /**
     * @throws ErroredException
     */
    public static function create(
        User         $actor, string $name, VisibilityEnum $visibility, int $Width, int $Height, int $HorizontalStart, int $VerticalStart, int $Opacity = 100,
        string       $Content = '#userid# #date#', string $ContentColour = "#000000", int $ContentSize = 10, ImageGravityEnum $ContentPosition = ImageGravityEnum::Center,
        string       $ContentBorderColour = "#000000", int $ContentBorderWeight = 1,
        UploadedFile $file = null, string $description = null): SignatureService
    {
        $signature = new DMSSignature();
        $signature->fill([
            "SignatureId" => self::_id(),
            "Name" => $name,
            "Description" => $description,
            "Visibility" => $visibility->value,
            "ImageId" => null,
            "SignatureHorizontalStart" => $HorizontalStart,
            "SignatureVerticalStart" => $VerticalStart,
            "SignatureOpacity" => $Opacity,
            "SignatureWidth" => $Width,
            "SignatureHeight" => $Height,
            "Content" => $Content,
            "ContentColour" => $ContentColour,
            "ContentSize" => $ContentSize,
            "ContentPosition" => $ContentPosition,
            "ContentBorderColour" => $ContentBorderColour,
            "ContentBorderWeight" => $ContentBorderWeight,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ])->save();

        activity()->causedBy($actor)->performedOn($signature)->event('create')->log('Created a signature : ' . $signature->Name);

        if ($file instanceof UploadedFile) {
            return (new self($signature))->setImage($file, $actor, false);
        }

        return new self($signature);
    }

    /**
     * @throws ErroredException
     */
    public function setImage(UploadedFile $file, User $actor, bool $log = true): static
    {
        $this->signature->update([
            'ImageId' => $this->signature->newDocument(ModulesEnum::DMS, $file, [], $actor)->Id
        ]);
        if ($log) {
            activity()->causedBy($actor)->performedOn($this->signature)->event('Image')->log('Signature Image updated : ' . $this->signature->Name);
        }
        return $this;
    }

    protected static function _id(): string
    {
        $number = DMSSignature::query()->withTrashed()->count();
        do {
            $number++;
            $slug = "Sign" . Str::of($number)->padLeft(4, '0');
        } while (DMSSignature::where('SignatureId', $slug)->withTrashed()->exists());

        return $slug;
    }

    /**
     * @throws ErroredException
     */
    public function sign(Document $document, User $actor, int $SignPages = 1, bool $queue = true): void
    {
        if ($queue) {
            SignDocumentJob::dispatch($document, $this->signature, $actor, $SignPages);
            return;
        }
        $this->_signDocument($this->signature, $document, $actor, $SignPages);
    }

}
