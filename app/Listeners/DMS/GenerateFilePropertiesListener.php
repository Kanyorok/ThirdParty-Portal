<?php

namespace App\Listeners\DMS;

use App\Events\DMS\DocumentUploadedEvent;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Services\DMS\Files\FileProperties;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class GenerateFilePropertiesListener implements ShouldQueue
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
        if ($event->generateProperties) {
            try {
                (new FileProperties($event->document))->setProperties(SystemHelper::user());
            } catch (ErroredException $e) {
                $this->fail($e);
            }
        }
    }
}
