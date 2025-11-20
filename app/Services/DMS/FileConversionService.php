<?php

namespace App\Services\DMS;

use App\Enums\Core\ExtensionsEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\DMS\Document;
use RuntimeException;
use Storage;

class FileConversionService
{
    protected ExtensionsEnum $extension;

    public function __construct(protected Document $document)
    {
        $ex = $this->document->ext();
        if (!$ex instanceof ExtensionsEnum) {
            throw new RuntimeException('Invalid file extension');
        }
        $this->extension = $ex;
    }

    /**
     * Returns a path to the converted PDF file
     * @throws ErroredException
     */
    public function convertToPDF(): ?string
    {
        $targetExtension = ExtensionsEnum::Pdf;
        if ($this->extension === $targetExtension) {
            return (new DocumentService($this->document))->getTempPath();
        }

        if (self::isInstalled()) {
            return $this->_convertToPDf();
        }

        return null;
    }

    public static function isInstalled(): bool
    {
        if (shell_exec("command -v soffice") === null) {
            SystemHelper::notifyAdmin('libreoffice is not found. Please install libreoffice package.');
            return false;
        }
        return true;
    }

    /**
     * Returns a path to the converted PDF file
     * @throws ErroredException
     */
    private function _convertToPDf(): ?string
    {
        $fileToConvert = (new DocumentService($this->document))->getTempPath();
        if ($fileToConvert === null || !file_exists($fileToConvert)) {
            return null;
        }
        $path = Storage::disk('temp')->path('');
        shell_exec("soffice --headless --convert-to pdf --outdir {$path} {$fileToConvert}");

        $info = pathinfo($fileToConvert);
        $convertedFile = $path . $info['filename'] . '.pdf';
        if (!file_exists($convertedFile)) {
            return null;
        }

        return $convertedFile;
    }

}
