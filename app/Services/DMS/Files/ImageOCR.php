<?php

namespace App\Services\DMS\Files;

use Exception;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;
use thiagoalessio\TesseractOCR\TesseractOcrException;

class ImageOCR extends FileExtraction
{
    public function processContent(): bool
    {
        if (!$this->extension->isImage()) {
            return false;
        }
        $name = $this->createTempFile();
        $content = $this->ocr(Storage::disk('temp')->path($name));
        $this->trashTempFile($name);
        if ($content !== '') {
            return $this->handleContent($content);
        }
        return $this->handleContent();
    }

    public function ocr(string $filePath): string
    {
        if (file_exists($filePath) === false) {
            return '';
        }

        try {
            return (new TesseractOCR ($filePath))->run();
        } catch (Exception|TesseractOcrException $e) {
        }

        return '';
    }
}
