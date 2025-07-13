<?php

namespace App\Services\DMS\Files;

class UnknownFileExtraction extends FileExtraction
{
    public function processContent(): bool
    {
        return $this->handleContent();
    }
}
