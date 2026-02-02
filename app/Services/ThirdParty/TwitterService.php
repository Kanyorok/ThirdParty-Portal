<?php

namespace App\Services\ThirdParty;

use App\Enums\Core\IntegrationsEnum;
use App\Exceptions\ErroredException;
use App\Models\CRM\Social;
use App\Models\Settings\APICredential;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use JsonException;
use Noweh\TwitterApi\Client as TwitterClient;

class TwitterService extends SocialService
{
    protected TwitterClient $tw;
    protected string $account_id;

    /**
     * @throws ErroredException
     */
    public function __construct(string $consumerKey = null, string $consumerSecret = null, string $accessToken = null, string $accessTokenSecret = null, string $bearerToken = null)
    {
        if (is_null($consumerKey) || is_null($consumerSecret) || is_null($accessToken) || is_null($accessTokenSecret) || is_null($bearerToken)) {//use db here.
            $cred = APICredential::query()->where('Integration', IntegrationsEnum::Twitter->value)->latest('Id')->first();
            if (! $cred instanceof APICredential) {
                throw new ErroredException('no twitter configuration');
            }
            $Config = $cred?->Configuration;
            $this->account_id = $Config->account_id;
            $this->tw = new TwitterClient([
                                           'account_id' => $Config->account_id,
                                           'access_token' => $Config->access_token,
                                           'access_token_secret' => $Config->access_token_secret,
                                           'consumer_key' => $Config->consumer_key,
                                           'consumer_secret' => $Config->consumer_secret,
                                           'bearer_token' => $Config->bearer_token,
                                           'free_mode' => (bool) $Config->is_free, // Optional
                                           'api_base_uri' => 'https://api.twitter.com/2/', // Optional
                                          ]);
        } else {
            $this->account_id = explode('-', $accessToken)[0];
            $this->tw = new TwitterClient([
                                           'account_id' => $this->account_id,
                                           'access_token' => $accessToken,
                                           'access_token_secret' => $accessTokenSecret,
                                           'consumer_key' => $consumerKey,
                                           'consumer_secret' => $consumerSecret,
                                           'bearer_token' => $bearerToken,
                //'free_mode' => (bool) $Config->is_free, // Optional
                                           'api_base_uri' => 'https://api.twitter.com/2/', // Optional
                                          ]);
        }
    }

    /*{#2264
    +"data": array:10 [
    0 => {#2207
    +"possibly_sensitive": false
    +"lang": "en"
    +"text": "@_mureithi_maina We apologize for the delayed response. Please find the production API base URL here: https://t.co/MJSsuY556y. For more help, call 0709376000 or 0207903030, WhatsApp 0709376000 or email support@kopokopo.com."
    +"created_at": "2024-11-20T09:18:58.000Z"
    +"entities": {#2190
          +"conversation_id": "1856609548284408165"
          +"public_metrics": {#2191
            +"retweet_count": 0
            +"reply_count": 0
            +"like_count": 0
            +"quote_count": 0
            +"bookmark_count": 0
            +"impression_count": 20
          }*/

    /**
     * @throws ErroredException
     */
    public function getMentions(): object
    {
        try {
            return $this->tw->timeline()->getRecentMentions($this->account_id)->performRequest();
        } catch (GuzzleException | JsonException | Exception $e) {
            Log::error('Get User id:');
            Log::error($e);
        }

        throw new ErroredException('could not fetch twitter user details');
    }

    /**
     * @throws ErroredException
     */
    public function getPosts()
    {
        try {
            return $this->tw->timeline()->getRecentTweets($this->account_id)->performRequest();
        } catch (GuzzleException | JsonException | Exception $e) {
            Log::error('Get User id:');
            Log::error($e);
        }

        throw new ErroredException('could not fetch twitter posts');
    }

    /**
     * @throws ErroredException
     */
    public function getPost(int $post_id)
    {
        try {
            return $this->tw->tweet()->fetch($post_id)->performRequest();
        } catch (GuzzleException | JsonException | Exception $e) {
            Log::error('Get User id:');
            Log::error($e);
        }

        throw new ErroredException('could not fetch twitter user details');
    }

    /**
     * +"data": {#2209
     * +"most_recent_tweet_id": "1865316008866492928"
     * +"location": "Nairobi Kenya"
     * +"public_metrics": {#2207
     * +"followers_count": 272
     * +"following_count": 587
     * +"tweet_count": 1216
     * +"listed_count": 5
     * +"like_count": 2718
     * +"media_count": 155
     * }
     * +"username": "_mureithi_maina"
     * +"profile_image_url": "https://pbs.twimg.com/profile_images/1350674047831601152/bZQjevpg_normal.jpg"
     * +"verified": false
     * +"id": "1326367836"
     * +"description": ""
     * +"verified_type": "none"
     * +"name": "Mureithi Maina"
     * +"created_at": "2013-04-04T07:37:04.000Z"
     *
     * @throws ErroredException
     */
    public function getUser(): object
    {
        try {
            $response = $this->tw->userMeLookup()->performRequest();
            if (! is_null($response)) {
                return $response;
            }
        } catch (GuzzleException | JsonException | Exception $e) {
            Log::error('Get User id:');
            Log::error($e);
        }

        throw new ErroredException('could not fetch twitter user details');
    }

    public function publish(Social $social): bool
    {
        if ($social->Type->value !== IntegrationsEnum::Twitter->value) {
            return false;
        }

        if (! is_null($social->Published_at)) {
            return true;
        }

        try {
            $Remote = $this->_createPost($social->Content, $social->images);
        } catch (Exception | ErroredException) {
            return false;
        }

        $social->update([
                         'Published_at' => Carbon::now(),
                         'RemoteId' => $Remote->data->id,
                         'Response' => json_encode($Remote),
                        ]);

        return true;
    }

    /**
     * @return object -> {"data":{"edit_history_tweet_ids":["1874732600935198942"],"id":"1874732600935198942","text":"Who can relate to this. https:\/\/t.co\/g192O6wOAu"}}
     * @throws ErroredException
     */
    protected function _createPost(string $message, Collection $medias = null): object
    {
        if ($medias instanceof Collection && $medias->count() > 0) {
            $media_ids = [];
            foreach ($medias as $media) {
                $media_ids[] = $this->_uploadMedia($media->Image);
            }

            try {
                return $this->tw->tweet()->create()
                    ->performRequest([
                                      'text' => $message,
                                      "media" => ["media_ids" => $media_ids],
                                     ]);
            } catch (GuzzleException | JsonException | Exception $e) {
                Log::error('Twitter images post tweet:');
                Log::error($e);
            }
        } else {
            try {
                return $this->tw->tweet()->create()
                    ->performRequest(['text' => $message]);
            } catch (GuzzleException | JsonException | Exception $e) {
                Log::error('Twitter post tweet:');
                Log::error($e);
            }
        }

        throw new ErroredException('an expected error occurred.');
    }

    /**
     * @throws ErroredException
     */
    private function _uploadMedia(string $mediaContent): string
    {
        try {
            $media_info = $this->tw->uploadMedia()->upload($mediaContent)['media_id'];
            if ($media_info) {
                return $media_info;
            }
        } catch (JsonException | Exception $e) {
            Log::error('Upload Twitter Image');
            Log::error($e);
        }

        throw new ErroredException('Uploading Image failed');
    }
}
