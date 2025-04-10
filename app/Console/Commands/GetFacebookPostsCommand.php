<?php

namespace App\Console\Commands;

use App\Enums\Core\IntegrationsEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\Social;
use App\Services\SocialMediaService;
use App\Services\ThirdParty\FacebookService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GetFacebookPostsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-facebook-posts-command';

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
        try {
            $service = new FacebookService();
        } catch (ErroredException $e) {
            return;
        }

        try {
            $posts = $service->getPosts();
        } catch (ErroredException $e) {
            return;
        }
        $actor = SystemHelper::user();
        foreach ($posts->data as $post) {
            $social = Social::query()->where('Type', IntegrationsEnum::Facebook->value)->where('RemoteId', $post->id)->first();
            if ($social instanceof Social) {
                if ($post->is_published && is_null($social->Published_at)) {
                    $social->Published_at = Carbon::parse($post->updated_time)->timezone(config('app.timezone'));
                }
                $views = (new FacebookService())->getViews($post->id);
                if (is_int($views)) {
                    $social->ViewsCount = $views;
                }

                $social->LikesCount = $post->likes->summary->total_count;
                $social->CommentsCount = $post->comments->summary->total_count;

                $social->save();

                //comments
                $service->syncComments($social);

                continue;
            }

            SocialMediaService::createFromFacebook($post, $actor);
        }

    }
}
