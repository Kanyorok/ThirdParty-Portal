<?php

namespace App\Listeners\Marketing;

use App\Events\Marketing\CompetitorRoachEvent;
use App\Helpers\StringHelper;
use App\Helpers\SystemHelper;
use App\Models\ThirdParies\Competitor;
use App\Services\ThirdParty\AIService;
use App\Spiders\CompetitorSpider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class CompetitorRoachListener implements ShouldQueue
{
    //   use InteractsWithQueue;
    public ?Competitor $competitor = null;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->competitor = null;
    }

    /**
     * Handle the event.
     */
    public function handle(CompetitorRoachEvent $event): void
    {
        $competitor = $event->competitor;
        $this->competitor = $competitor;

        $competitor->update(['Processing' => ['done' => 3, 'total' => 10]]);

        $items = Roach::collectSpider(
            CompetitorSpider::class,
            new Overrides(startUrls: [$competitor->Website]),
        );

        $competitor->update(['Processing' => ['done' => 5, 'total' => 10]]);
        $content = '';

        foreach ($items as $item) {
            //set logo url
            if ($item->has('logo')) {
                $this->setLogo($competitor, $item->get('logo'));
            }

            if ($item->has('landing')) {
                $landing = $item->get('landing');
                $content .= $landing;
            }

            if ($item->has('content')) {
                $content .= $item->get('content');
            }
        }

        $competitor->update(['Processing' => ['done' => 7, 'total' => 10]]);

        if (Str::length($content) > 0) {
            try {
                $data = (new AIService())->competitor(Str::limit(StringHelper::removeAccessibility(StringHelper::cleanHtml($content)), 30000));
            } catch (\Exception | \Throwable) {
                $data = null;
            }
            $competitor->update(['Processing' => ['done' => 9, 'total' => 10]]);

            if (is_array($data)) {
                if ($event->clear) {
                    $competitor->products()->delete();
                }
                if (array_key_exists('name', $data)) {
                    $competitor->CompetitorName = $data['name'];
                }
                if (array_key_exists('clients', $data) && is_numeric($data['clients']) && $data['clients'] > 0) {
                    $competitor->Clients = $data['clients'];
                }
                if (array_key_exists('summary', $data)) {
                    $competitor->Notes = $data['summary'];
                }
                if (array_key_exists('email', $data)) {
                    $competitor->Email = $data['email'];
                }
                if (array_key_exists('core_business', $data)) {
                    $competitor->CoreBusiness = $data['core_business'];
                }
                $competitor->save();

                if (array_key_exists('products', $data)) {
                    foreach ($data['products'] as $product) {
                        if (!array_key_exists('name', $product)) {
                            continue;
                        }
                        //"", "Limit", "", "OtherCharges", "", "", "Clients",
                        $competitor->products()->create([
                                                         'Name'             => $product['name'],
                                                         'Notes'            => array_key_exists('description', $product) ? ($product['description']) : null,
                                                         'InterestRate'     => array_key_exists('interest', $product) ? ($product['interest']) : null,
                                                         'RepaymentPeriod'  => array_key_exists('period', $product) ? ($product['period']) : null,
                                                         'SecurityRequired' => array_key_exists('security', $product) ? ($product['security']) : null,
                                                         'CreatedBy'        => SystemHelper::user()->Id,
                                                         'ModifiedBy'       => SystemHelper::user()->Id,
                                                        ]);
                    }
                }
                // 'name', 'clients', 'summary', 'email', 'core_business','products', 'name','interest','period','description',''
            }
        }

        $competitor->update(['Processing' => null]);
    }

    private function setLogo(Competitor $competitor, string $logo): void
    {
        try {
            if (Http::get($logo)->successful()) {
                $competitor->setAvatarFromURL($logo, SystemHelper::user(), 'Logo');
            }
        } catch (\Exception | ConnectionException $e) {
            Log::error('Set Image Error: ' . $logo);
            Log::error($e);
        }

        $competitor->update(['Processing' => ['done' => 6, 'total' => 10]]);
    }

    public function failed(\Exception $exception)
    {
        // Reset the processing state
        $this->competitor?->update(['Processing' => null]);

        // Log the error
        Log::error(__CLASS__ . ' Failed:');
        Log::error($exception);

        // Notify the initiator
        if ($this->competitor && $initiator = $this->competitor->creator) {
            // Notification::send($initiator, new QueueProcessingFailedNotification($this->competitor, $exception->getMessage()));
        }
    }
}
