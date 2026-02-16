<?php

namespace App\Services\DMS\Files;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\DMS\ContentEnum;
use App\Exceptions\ErroredException;
use App\Models\DMS\Document;
use App\Services\DMS\AutoTaggingService;
use App\Services\DMS\DocumentService;
use App\Services\DMS\TextCleanerService;
use DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Storage;
use Throwable;

abstract class FileExtraction extends DocumentService
{
    protected ExtensionsEnum $extension;

    public function __construct(Document $document)
    {
        parent::__construct($document);
        $ex = $this->document->ext();
        if (! $ex instanceof ExtensionsEnum) {
            throw new RuntimeException('Invalid file extension');
        }
        $this->extension = $ex;
    }

    abstract public function processContent(): bool;

    protected function handleContent(string $content = null): bool
    {
        try {
            return DB::transaction(function () use ($content) {
                if (is_string($content) && $content !== '') {
                    (new AutoTaggingService($this->document, $content))->tag($this->document->creator, ContentEnum::Body);
                    $this->document->current->update(['Blob' => (new TextCleanerService($content))->getShuffledText()]);
                }

                (new AutoTaggingService($this->document, $this->document->Name))->tag($this->document->creator, ContentEnum::Title);

                return true;
            }, 2);
        } catch (Throwable $e) {
            Log::error('Generate Document Blog & Auto Tagging Failed : ' . $e);

            return false;
        }
    }

    /**
     * @throws ErroredException
     */
    protected function createTempFile(): ?string
    {
        $name = Uuid::uuid4()->toString() . '.' . $this->extension->value;

        return (Storage::disk('temp')->put($name, $this->getFileContent(false))) ? $name : null;
    }

    protected function trashTempFile(string $name): void
    {
        Storage::disk('temp')->delete($name);
    }
}
