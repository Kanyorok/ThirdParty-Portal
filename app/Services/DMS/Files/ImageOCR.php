<?php

namespace App\Services\DMS\Files;

use Exception;
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
        $content = $this->ocr(storage_path('app/temp') . $name);
        $this->trashTempFile($name);
        if ($content !== '') {
            return $this->handleContent($content);
        }
        return $this->handleContent();
    }

    private function ocr(string $filePath): string
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
