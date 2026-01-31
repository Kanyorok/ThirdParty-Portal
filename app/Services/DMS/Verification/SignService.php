<?php

namespace App\Services\DMS\Verification;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\DMS\ImageGravityEnum;
use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\DMS\DMSSignature;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentSignature;
use App\Services\DMS\DocumentService;
use App\Services\DMS\FileConversionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Storage;
use Throwable;

abstract class SignService
{
    private string $font_path = '';
    private int $docPages;
    private int $sign_start_h = 10;
    private int $sign_start_v = 10;
    private int $sign_width = 500;
    private int $sign_height = 500;
    private int $opacity = 69;
    private int $contentSize = 20;
    private string $contentFill = '#0b0005';
    private string $contentStoke = '#a8004b';
    private int $strokewidth = 0;
    private ImageGravityEnum $contentLocation = ImageGravityEnum::Center;
    private string $content = '';
    private string $tempDocument;
    private bool $signAll = false;

    /**
     * @throws ErroredException
     */
    final protected function _signDocument(DMSSignature $signature, Document $document, User $actor, int $SignPages = 1): DocumentService
    {
        $this->setTempDocument($document);
        $this->_setProperties($signature, $actor);
        $pages = $this->getPagesCount();
        if ($pages < 0) {
            throw new ErroredException('Failed to sign document, Could not find any pages.');
        }

        if ($pages === $SignPages || $SignPages === 0) {
            $this->signAll = true;
            $SignPages = $pages;
        } else {
            $this->signAll = false;
            $SignPages = (abs($SignPages) > $pages) ? $pages : (int)abs($SignPages);
        }

        $image_path = ($signature->image instanceof Document && $signature->image->ext()?->isImage()) ? (new DocumentService($signature->image))->getTempPath() : storage_path('core/blank_sign.png');
        $stampSign = $this->_getStampSign($image_path);
        if (empty($stampSign)) {
            throw new ErroredException('Failed to create stamp to sign');
        }

        $paths = collect();
        for ($i = 1; $i <= $SignPages; $i++) {
            $paths->add($this->_addStampToPage($this->_convertPageImage($i), $stampSign));
        }

        $path = $this->_mergeBack($this->_convertImageToPDF($paths->toArray()));

        unlink($stampSign);
        unlink($this->tempDocument);

        try {
            return DB::transaction(function () use ($SignPages, $signature, $document, $path, $actor) {
                DocumentSignature::create([
                    'DocumentId' => $document->Id,
                    'SignatureId' => $signature->Id,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                    "Extra" => [
                        'pages' => $SignPages,
                        'dimensions' => [
                            'width' => $this->sign_width,
                            'height' => $this->sign_height,
                        ],
                        'position' => [
                            'x' => $this->sign_start_h,
                            'y' => $this->sign_start_v,
                        ],
                    ],
                    "Content" => $this->content,
                ]);

                return (new DocumentService($document))->newVersionFile($path, $actor);
            });
        } catch (Throwable $e) {
            Log::error('Error signing document: ' . $e);
        }

        throw new ErroredException('Signing document failed: DB');
    }

    /**
     * @throws ErroredException
     */
    protected function setTempDocument(Document $document): void
    {
        $docService = new DocumentService($document);
        if ($docService->type->canSign()) {
            throw new ErroredException('Document cannot be signed');
        }

        if ($docService->isCheckedOut() || $docService->isHold()) {
            throw new ErroredException('Document is checked out or is in legal hold');
        }

        if ($docService->type === ExtensionsEnum::Pdf) {
            $file = $docService->getTempPath();
            if (is_string($file) && file_exists($file)) {
                $this->tempDocument = $docService->getTempPath();

                return;
            }

            throw new ErroredException('Decrypting file failed, try again later.');
        }

        //convert to PDF
        $file = (new FileConversionService($document))->convertToPdf();
        if (is_string($file) && file_exists($file)) {
            $this->tempDocument = $docService->getTempPath();

            return;
        }

        throw new ErroredException('Converting file failed, try again later.');
    }

    private function _setProperties(DMSSignature $signature, User $actor): void
    {
        $this->font_path = storage_path('fonts/signature.ttf');
        $this->sign_start_h = $signature->SignatureHorizontalStart;
        $this->sign_start_v = $signature->SignatureVerticalStart;
        $this->sign_width = $signature->SignatureWidth;
        $this->sign_height = $signature->SignatureHeight;
        $this->opacity = $signature->SignatureOpacity;
        $this->contentSize = $signature->ContentSize;
        $this->contentStoke = $signature->ContentBorderColour;
        $this->strokewidth = $signature->ContentBorderWeight;
        $this->contentFill = $signature->ContentColour;
        $this->contentLocation = $signature->ContentPosition;

        $this->content = Str::of($signature->Content)->trim()->replace(
            [" ", '#name#', '#userid#', '#datetime#', '#date#'],
            ["\\n", $actor->Name, $actor->UserID, now()->format('d M Y H:i'), now()->format('d M Y')]
        )->limit(100, '>>')->toString();
    }

    public function getPagesCount(): int
    {
        if (isset($this->docPages)) {
            return $this->docPages;
        }
        if (! isset($this->tempDocument) || ! file_exists($this->tempDocument)) {
            return 0;
        }

        $string = shell_exec("pdfinfo {$this->tempDocument} | grep Pages");
        if (is_string($string) && preg_match('/Pages:\s+(\d+)/', $string, $matches)) {
            $this->docPages = (int)$matches[1];

            return $this->docPages;
        }

        return 0;
    }

    /**
     * Sign & Stamp Combined to sign documents
     */
    private function _getStampSign(string $stamp): string
    {
        $path = Storage::disk('temp')->path(Str::uuid()->toString() . '.png');

        shell_exec("convert {$stamp} -stroke \"{$this->contentStoke}\" -strokewidth {$this->strokewidth} -font \"{$this->font_path}\" -weight Bold  -pointsize {$this->contentSize} -fill \"{$this->contentFill}\" -gravity {$this->contentLocation->name} -annotate +10+10 \"{$this->content}\" {$path}");

        if (! file_exists($path)) {
            return '';
        }

        if (! str_ends_with($stamp, 'blank_sign.png')) {
            unlink($stamp);
        }

        return $path;
    }

    /**
     * @throws ErroredException
     */
    private function _addStampToPage(string $imagePath, string $signature): string
    {
        $path = Storage::disk('temp')->path(Str::uuid()->toString() . '.png');
        shell_exec("composite  -geometry {$this->sign_width}x{$this->sign_height}+{$this->sign_start_h}+{$this->sign_start_v} -dissolve {$this->opacity}% $signature $imagePath $path");

        if (! file_exists($path)) {
            unlink($imagePath);

            throw new ErroredException('Failed to add stamp to page');
        }
        //remove tmp
        unlink($imagePath);

        return $path;
    }

    /**
     * @throws ErroredException
     */
    private function _convertPageImage(int $page): string
    {
        $path = Storage::disk('temp')->path(Str::uuid()->toString() . '.png');

        if ($page < 1 || $page > $this->getPagesCount()) {
            throw new ErroredException('Page number is invalid');
        }

        --$page;

        shell_exec("convert -density 300 {$this->tempDocument}[{$page}] {$path}");

        if (! file_exists($path)) {
            throw new ErroredException("Page number {$page} is invalid");
        }

        return $path;
    }

    private function _mergeBack(string $signedPath): string
    {
        if ($this->signAll || $this->getPagesCount() <= 1) {
            return $signedPath;
        }

        $path = Storage::disk('temp')->path(Str::uuid()->toString() . '.pdf');

        shell_exec("pdftk A={$this->tempDocument} B={$signedPath} shuffle B A2-end output {$path}");

        if (! file_exists($path)) {
            return '';
        }
        //remove tmp
        unlink($signedPath);

        return $path;
    }

    private function _convertImageToPDF(array $paths): string
    {
        $path = Storage::disk('temp')->path(Str::uuid()->toString() . '.pdf');

        shell_exec("convert -density 150 " . implode(' ', $paths) . " {$path}");

        foreach ($paths as $imagePath) {
            unlink($imagePath);
        }

        return (! file_exists($path)) ? '' : $path;
    }
}
