<?php

namespace App\Services\DMS\Files;

use App\Enums\Core\ExtensionsEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\DMS\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Log;
use Ramsey\Uuid\Uuid;

class PdfExtraction extends FileExtraction
{
    /**
     * @throws ErroredException
     */
    public function processContent(): bool
    {
        if ($this->type->value !== ExtensionsEnum::Pdf->value) {
            return false;
        }
        $name = $this->createTempFile();
        $content = $this->simpleExtractText(Storage::disk('temp')->path($name));
        if ($content === '') {
            $content = $this->ocr(Storage::disk('temp')->path($name));
        }
        $this->trashTempFile($name);
        if ($content !== '') {
            return $this->handleContent($content);
        }

        return $this->handleContent();
    }

    public function simpleExtractText(string $filePath): string
    {
        if (file_exists($filePath) === false) {
            return '';
        }

        if (! self::isInstalled()) {
            return '';
        }

        $output = shell_exec("pdftotext -q '{$filePath}' -");
        if (is_string($output) === false) {
            return '';
        }

        return Str::of($output)->replace('/\s+/', ' ')->trim()->toString();
    }

    public static function isInstalled(): bool
    {
        if (shell_exec("command -v pdfinfo") === null) {
            SystemHelper::notifyAdmin('pdfinfo command not found. Please install poppler-utils package.');

            return false;
        }

        return true;
    }

    public function ocr(string $filePath): string
    {
        if (! self::isInstalled()) {
            return '';
        }

        if (file_exists($filePath) === false) {
            return '';
        }

        $outputDir = Storage::disk('temp')->path('');

        $outputPrefix = Uuid::uuid4()->toString(); // n ...1.jpg
        $jpegQuality = 90;

        /*$pdfinfoOutput = shell_exec("pdfinfo " . escapeshellarg($filePath) . " | grep Pages:");
        preg_match('/Pages:\s*(\d+)/', $pdfinfoOutput, $matches);
        $numPages = isset($matches[1]) ? (int)$matches[1] : 0;

        if ($numPages === 0) {
            return '';
        }*/

        $output = shell_exec(sprintf(
            'pdftocairo -jpeg -jpegopt quality=%d %s %s 2>&1',
            $jpegQuality,
            escapeshellarg($filePath),
            escapeshellarg($outputDir . $outputPrefix)
        ));

        if (str_contains($output, 'Error:')) {
            Log::error("Error executing pdftocairo: " . $output);

            return '';
        }

        $content = '';
        $id = 1;
        $filename = $outputPrefix . '-' . $id . '.jpg';
        $filePath = $outputDir . $filename;
        if (! file_exists($filePath)) {
            return '';
        }
        do {
            $content .= (new ImageOCR(new Document(['MimeType' => ExtensionsEnum::Jpeg->getMimeType()])))->ocr(Storage::disk('temp')->path($filename));
            $this->trashTempFile($filename);
            $id++;
            $filename = $outputPrefix . '-' . $id . '.jpg';
            $filePath = $outputDir . $filename;
        } while (file_exists($filePath));

        return $content;
    }
}
