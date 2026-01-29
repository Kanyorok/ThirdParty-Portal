<?php

namespace App\Listeners\DMS;

use App\Events\DMS\DocumentUploadedEvent;
use App\Services\DMS\FileExtractionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

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
    public function handle(DocumentUploadedEvent $event): void
    {
        if (! (new FileExtractionService($event->document))->searchAndTags()) {
            Log::error('Could not process file content extraction for tags and search : ' . $event->document->DocumentId);
            $this->fail('Could not process file content extraction for tags and search');
        }
    }
}
