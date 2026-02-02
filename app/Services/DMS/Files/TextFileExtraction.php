<?php

namespace App\Services\DMS\Files;

use Exception;
use Illuminate\Support\Facades\Storage;

class TextFileExtraction extends FileExtraction
{
    public function processContent(): bool
    {
        if (! $this->extension->isText()) {
            return false;
        }
        $name = $this->createTempFile();
        $content = $this->getContent(Storage::disk('temp')->path($name));
        $this->trashTempFile($name);
        if ($content !== '') {
            return $this->handleContent($content);
        }

        return $this->handleContent();
    }

    private function getContent(string $filePath): string
    {
        if (! file_exists($filePath)) {
            return '';
        }

        try {
            $content = file_get_contents($filePath);
            if (is_string($content)) {
                return $content;
            }
        } catch (Exception $e) {
        }

        return '';
    }
}
