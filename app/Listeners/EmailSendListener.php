<?php

namespace App\Listeners;

use App\Events\EmailSendEvent;
use App\Services\CRMEmailService;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailSendListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(EmailSendEvent $event): void
    {
        (new CRMEmailService($event->crmEmail))->send(true);
    }
}
