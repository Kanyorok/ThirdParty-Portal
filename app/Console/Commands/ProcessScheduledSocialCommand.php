<?php

namespace App\Console\Commands;

use App\Enums\Core\IntegrationsEnum;
use App\Helpers\SystemHelper;
use App\Models\Social;
use App\Services\SocialMediaService;
use Illuminate\Console\Command;

class ProcessScheduledSocialCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-scheduled-social-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $posts = Social::query()->where('t_Socials.Scheduled_at', '<', now()->addMinutes(15))
            ->whereNull('Published_at')->get();
        foreach ($posts as $post) {
            if (in_array($post->Type->value, [IntegrationsEnum::Twitter->value, IntegrationsEnum::Facebook->value], true)) {
                (new SocialMediaService($post))->publish();
                continue;
            }
            SystemHelper::notifyAdmin('Unknown Social to post: ' . $post->Id);
        }
    }
}
