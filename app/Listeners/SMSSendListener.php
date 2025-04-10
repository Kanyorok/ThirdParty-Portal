<?php

namespace App\Listeners;

use App\Events\SMSSendEvent;
use App\Services\SMSService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SMSSendListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SMSSendEvent $event): void
    {
        (new SMSService($event->sms))->send(true);
    }
}
