<?php

namespace App\Console\Commands;

use App\Enums\Core\IntegrationsEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\CRM\Social;
use App\Services\SocialMediaService;
use App\Services\ThirdParty\TwitterService;
use Illuminate\Console\Command;

class GetTwitterPostsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-twitter-posts-command';

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
            $service = new TwitterService();
        } catch (ErroredException $e) {
            $this->error($e->getMessage());

            return;
        }

        try {
            $posts = $service->getPosts();
        } catch (ErroredException $e) {
            $this->error($e->getMessage());

            return;
        }

        // $posts = json_decode(file_get_contents(storage_path('test.json')), true, 512, JSON_THROW_ON_ERROR);
        //$posts =str_replace(PHP_EOL,'', $content);
        //$posts = ((object)json_decode(json_encode($posts, JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT), false, 512, JSON_THROW_ON_ERROR));

        $actor = SystemHelper::user();
        foreach ($posts->includes->tweets as $tweet) {
            $social = Social::query()->where('Type', IntegrationsEnum::Twitter->value)->where('RemoteId', $tweet->id)->first();
            if ($social instanceof Social) {
                $social->fill([
                               'LikesCount' => $tweet->public_metrics->like_count,
                               'CommentsCount' => $tweet->public_metrics->reply_count,
                               'ViewsCount' => $tweet->public_metrics->impression_count,
                               'Response' => json_encode($tweet),
                              ])->save();

                continue;
            }

            $tweetCollection = collect(['tweet' => $tweet]);
            $medias = $tweet->media_metadata ?? null;

            $mediaCollection = collect();
            if (! is_null($medias)) {
                foreach ($medias as $media) {
                    if (is_string($media->media_key)) {
                        $response = $this->getMedia($posts->includes->media, $media->media_key);
                        if (! is_null($response)) {
                            $mediaCollection->push($response);
                        }
                    }
                }
            }
            SocialMediaService::createFromTwitter($tweetCollection->merge(['media' => $mediaCollection->toArray()]), $actor);
        }
    }

    public function getMedia($medias, string $media_key)
    {
        foreach ($medias as $media) {
            if ($media->media_key === $media_key) {
                return $media;
            }
        }

        return null;
    }
}
/*
{
    "author_id": "1326367836",
    "reply_settings": "everyone",
    "possibly_sensitive": false,
+"entities": {#2251
    +"urls": array:1 [
        0 => {#2242
        +"start": 11
        +"end": 34
        +"url": "https://t.co/FFzwFVKGO7"
        +"expanded_url": "https://x.com/_mureithi_maina/status/1869306682465267892/photo/1"
        +"display_url": "pic.x.com/FFzwFVKGO7"
        +"media_key": "3_1869306612331999232"
          }
        ]
      }
      +"text": "can relate https://t.co/FFzwFVKGO7"
+"conversation_id": "1869306682465267892"
+"created_at": "2024-12-18T09:00:17.000Z"
+"lang": "en"
+"display_text_range": array:2 [
    0 => 0
        1 => 10
      ]
      +"id": "1869306682465267892"
+"edit_history_tweet_ids": array:1 [
    0 => "1869306682465267892"
]
+"edit_controls": {#2207
    +"edits_remaining": 5
    +"is_edit_eligible": true
    +"editable_until": "2024-12-18T10:00:17.000Z"
      }
      +"public_metrics": {#2240
    +"retweet_count": 0
    +"reply_count": 1
    +"like_count": 1
    +"quote_count": 0
    +"bookmark_count": 0
    +"impression_count": 5
      }
      +"attachments": {#2241
    +"media_keys": array:1 [
        0 => "3_1869306612331999232"
    ]
      }
    }
]
+"includes": {#2258
    +"media": array:1 [
        0 => {#2248
        +"type": "photo"
        +"height": 921
        +"media_key": "3_1869306612331999232"
        +"width": 1080
        +"url": "https://pbs.twimg.com/media/GfEcdbxWcAALN9x.jpg"
      }
    ]
    +"users": array:1 [
        0 => {#2260
        +"public_metrics": {#2259
            +"followers_count": 270
            +"following_count": 588
            +"tweet_count": 1223
            +"listed_count": 5
            +"like_count": 2731
            +"media_count": 156
        }
        +"entities": {#2263
            +"url": {#2262
                +"urls": array:1 [
                    0 => {#2261
                    +"start": 0
                    +"end": 23
                    +"url": "https://t.co/MwUysTLH50"
                    +"expanded_url": "https://www.razorinformatics.co.ke"
                    +"display_url": "razorinformatics.co.ke"
              }
            ]
          }
          +"description": {#2265
                +"mentions": array:1 [
                    0 => {#2264
                    +"start": 82
                    +"end": 96
                    +"username": "r_informatics"
              }
            ]
          }
        }
        +"receives_your_dm": true
        +"verified": false
        +"location": "Nairobi Kenya"
        +"subscription_type": "None"
        +"description": """
          • Father\n
          • Full Stack Web Developer\n
          • Tech Enthusiast\n
          • Founder Razor Informatics @r_informatics\n
          Cogito, Ergo Sum 😏
          """
        +"url": "https://t.co/MwUysTLH50"
        +"profile_image_url": "https://pbs.twimg.com/profile_images/1350674047831601152/bZQjevpg_normal.jpg"
        +"verified_type": "none"
        +"id": "1326367836"
        +"name": "Mureithi Maina"
        +"protected": false
        +"most_recent_tweet_id": "1870083531084361874"
        +"username": "_mureithi_maina"
        +"created_at": "2013-04-04T07:37:04.000Z"
        +"profile_banner_url": "https://pbs.twimg.com/profile_banners/1326367836/1405057889"
      }
    ]
    +"tweets": array:1 [
        0 => {#2266
        +"author_id": "1326367836"
        +"reply_settings": "everyone"
        +"media_metadata": array:1 [
            0 => {#2267
            +"media_key": "3_1869306612331999232"
          }
        ]
        +"possibly_sensitive": false
        +"entities": {#2269
            +"urls": array:1 [
                0 => {#2268
                +"start": 11
                +"end": 34
                +"url": "https://t.co/FFzwFVKGO7"
                +"expanded_url": "https://x.com/_mureithi_maina/status/1869306682465267892/photo/1"
                +"display_url": "pic.x.com/FFzwFVKGO7"
                +"media_key": "3_1869306612331999232"
            }
          ]
        }
        +"text": "can relate https://t.co/FFzwFVKGO7"
        +"conversation_id": "1869306682465267892"
        +"created_at": "2024-12-18T09:00:17.000Z"
        +"lang": "en"
        +"display_text_range": array:2 [
            0 => 0
          1 => 10
        ]
        +"id": "1869306682465267892"
        +"edit_history_tweet_ids": array:1 [
            0 => "1869306682465267892"
        ]
        +"edit_controls": {#2270
            +"edits_remaining": 5
            +"is_edit_eligible": true
            +"editable_until": "2024-12-18T10:00:17.000Z"
        }
        +"public_metrics": {#2271
            +"retweet_count": 0
            +"reply_count": 1
            +"like_count": 1
            +"quote_count": 0
            +"bookmark_count": 0
            +"impression_count": 5
        }
        +"attachments": {#2272
            +"media_keys": array:1 [
                0 => "3_1869306612331999232"
            ]
        }
      }
    ]
  }*/
