<?php

namespace App\Listeners\DMS;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\DMS\ContentEnum;
use App\Events\DMS\DocumentCreatedEvent;
use App\Models\DMS\Document;
use App\Services\DMS\AutoTaggingService;
use App\Services\DMS\DocumentService;
use App\Services\DMS\TextCleanerService;
use DB;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;
use thiagoalessio\TesseractOCR\TesseractOcrException;
use Throwable;

class GenerateDocumentBlobListener implements ShouldQueue
{
    use InteractsWithQueue;


    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(DocumentCreatedEvent $event): void
    {
        $document = $event->document;
        $ext = $document->ext();
        if (!$ext instanceof ExtensionsEnum) {
            return;
        }
        $service = new DocumentService($document);
        if ($ext->isImage()) {
            $this->ocrImage($service, $ext);
            return;
        }
    }

    private function ocrImage(DocumentService $service, ExtensionsEnum $extension): void
    {
        $name = Uuid::uuid4()->toString() . $extension->value;
        //decrypt temporarily
        if (!Storage::disk('temp')->put($name, $service->getFileContent(false))) {
            return;
        }

        try {
            $content = (new TesseractOCR (storage_path('app/temp') . $name))->run();
        } catch (Exception|TesseractOcrException $e) {
        }

        //  encrypt immediately after.
        Storage::disk('temp')->delete($name);

        if (isset($content)) {
            $this->handleContent($service->document, $content);
        }
    }

    private function handleContent(Document $document, string $content): void
    {
        try {
            DB::transaction(static function () use ($document, $content) {
                (new AutoTaggingService($document, $content))->tag($document->creator, ContentEnum::Body);
                (new AutoTaggingService($document, $document->Name))->tag($document->creator, ContentEnum::Title);

                $document->current->update(['Blob' => (new TextCleanerService($content))->getShuffledText()]);
            }, 2);
        } catch (Throwable $e) {
            Log::error('Generate Document Blog & Auto Tagging Failed : ');
            Log::error($e);
            $this->fail($e);
        }

    }


}
