<?php

namespace App\Listeners\Marketing;

use App\Events\Marketing\NewCampaignEvent;
use App\Services\Marketing\CampaignService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessContactsListener implements ShouldQueue
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
    public function handle(NewCampaignEvent $event): void
    {
        $campaign = $event->campaign;
        /* DB::transaction(static function () use ($campaign, $event) {
             DB::statement('SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED');*/
        (new CampaignService($campaign))->syncFromList($event->actor);
        if ($event->autoSend) {
            (new CampaignService($campaign))->run($event->actor);
        } else {
            $campaign->update(['Processing' => false]);
        }
        /*  }, 2);*/
    }
}
