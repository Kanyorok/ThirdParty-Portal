<?php

namespace App\Services\DMS;

use App\Enums\Core\ExtensionsEnum;
use App\Models\DMS\Document;
use App\Services\DMS\Files\ImageOCR;
use App\Services\DMS\Files\TextFileExtraction;
use App\Services\DMS\Files\UnknownFileExtraction;
use App\Services\DMS\Files\WordDocumentExtractor;
use RuntimeException;

class FileExtractionService
{
    protected ExtensionsEnum $extension;

    public function __construct(protected Document $document)
    {
        $ex = $this->document->ext();
        if ($ex instanceof ExtensionsEnum) {
            throw new RuntimeException('Invalid file extension');
        }
        $this->extension = $ex;
    }

    public function searchAndTags(): bool
    {
        if ($this->extension->isImage()) {
            return (new ImageOCR($this->document))->processContent();
        }
        if ($this->extension->isDocument()) {
            return (new WordDocumentExtractor($this->document))->processContent();
        }

        if ($this->extension->isText()) {
            return (new TextFileExtraction($this->document))->processContent();
        }

        return (new UnknownFileExtraction($this->document))->processContent();
    }


}
