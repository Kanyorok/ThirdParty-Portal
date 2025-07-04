<?php

namespace App\Services\DMS\Files;

class UnknownFile extends FileExtraction
{
    public function processContent(): bool
    {
        return $this->handleContent();
    }
}
