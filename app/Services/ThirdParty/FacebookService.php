<?php

namespace App\Services\ThirdParty;

use App\Enums\Core\IntegrationsEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\CRM\Social;
use App\Models\DMS\Image;
use App\Models\Settings\APICredential;
use Carbon\Carbon;
use Exception;
use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\Comment;
use FacebookAds\Object\Fields\CommentFields;
use FacebookAds\Object\Fields\PageFields;
use FacebookAds\Object\Fields\PagePostFields;
use FacebookAds\Object\Page;
use FacebookAds\Object\PagePost;
use FacebookAds\Object\User;
use FacebookAds\Object\Values\CommentOrderValues;
use FacebookAds\Object\Values\InsightsResultPeriodValues;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;
use SensitiveParameter;
use Throwable;

class FacebookService extends SocialService
{
    private const PAGE_POST_FIELDS = [
                                      PagePostFields::ID,
                                      PagePostFields::ADMIN_CREATOR,
                                      PagePostFields::IS_HIDDEN,
                                      PagePostFields::IS_ELIGIBLE_FOR_PROMOTION,
                                      PagePostFields::CREATED_TIME,
                                      PagePostFields::UPDATED_TIME,
                                      PagePostFields::MESSAGE,
                                      PagePostFields::PICTURE,
                                      PagePostFields::FULL_PICTURE,
                                      PagePostFields::IS_PUBLISHED,
                                      PagePostFields::SCHEDULED_PUBLISH_TIME,
                                      PagePostFields::SHARES,
                                      "likes.limit(0).summary(true)",
                                      "comments.limit(0).summary(true)",
                                      "reactions.summary(true)",
                                     ];
    private const PAGE_FIELDS = [
                                 PageFields::ID,
                                 PageFields::NAME,
                                 PageFields::NEW_LIKE_COUNT,
                                 PageFields::BIO,
                                 PageFields::WEBSITE,
                                 PageFields::WHATSAPP_NUMBER,
                                 PageFields::CONTACT_ADDRESS,
                                 PageFields::ABOUT,
                                 PageFields::CONNECTED_INSTAGRAM_ACCOUNT,
                                 PageFields::COVER,
                                 PageFields::WHATSAPP_NUMBER,
                                 PageFields::PAGE_TOKEN,
                                ];

    private const COMMENT_FIELDS = [
                                    CommentFields::ID,
                                    CommentFields::CAN_COMMENT,
                                    CommentFields::CAN_HIDE,
                                    CommentFields::CAN_LIKE,
                                    CommentFields::CREATED_TIME,
                                    CommentFields::LIKE_COUNT,
                                    CommentFields::COMMENT_COUNT,
                                    CommentFields::MESSAGE,
                                    CommentFields::FROM,
                                    CommentFields::ATTACHMENT,
                                    CommentFields::CAN_REMOVE,/* CommentFields::PARENT,*/
                                    CommentFields::IS_HIDDEN,
                                   ];

    public const PAGE_PERMISSIONS = [
                                     'read_insights',
                                     'publish_video',
                                     'pages_manage_cta',
                                     'pages_manage_instant_articles',
                                     'pages_show_list',
                                     'read_page_mailboxes',
                                     'pages_messaging',
                                     'pages_messaging_subscriptions',
        // 'instagram_basic', 'instagram_manage_comments', 'instagram_manage_insights', 'instagram_content_publish','instagram_manage_messages', 'instagram_branded_content_brand','instagram_branded_content_creator', 'instagram_branded_content_ads_brand', 'instagram_manage_events',
                                     'page_events',
                                     'pages_read_engagement',
                                     'pages_manage_metadata',
                                     'pages_read_user_content',
                                     'pages_manage_ads',
                                     'pages_manage_posts',
                                     'pages_manage_engagement',
                                     'whatsapp_business_management',
                                     'whatsapp_business_messaging',
                                     'leads_retrieval',
                                     'business_management',
                                     'ads_management',
                                     'ads_read',
                                     'catalog_management',
                                     'manage_fundraisers',
                                     'manage_app_solution',
                                     'public_profile',
                                    ];

    protected const RENEW_DAYS_BEFORE_EXPIRE = 14;
    protected Api $api;
    protected ?APICredential $credential = null;
    protected int $PageID, $AppId;
    protected string $appSecret, $pageToken;

    /**
     * @throws ErroredException
     */
    public function __construct(int $AppId = null, #[SensitiveParameter] string $appSecret = null, #[SensitiveParameter] string $pageToken = null, int $pageID = null)
    {
        if (is_null($AppId) || is_null($appSecret) || is_null($pageToken) || is_null($pageID)) {//use db here.
            $cred = APICredential::query()->where('Integration', IntegrationsEnum::Facebook->value)->latest('Id')->first();
            if (!$cred instanceof APICredential) {
                throw new ErroredException('no facebook configuration');
            }
            $this->credential = $cred;
            $Config = $cred?->Configuration;
            $this->PageID = $Config->page_id;
            $this->AppId = $Config->app_id;
            $this->appSecret = $Config->app_secret;
            $this->pageToken = $Config->page_token;
        } else {
            $this->PageID = $pageID;
            $this->appSecret = $appSecret;
            $this->pageToken = $pageToken;
            $this->AppId = $AppId;
        }
        $this->api = Api::init($this->AppId, $this->appSecret, $this->pageToken);
        if (config('app.debug')) {
            $this->api->setLogger(new CurlLogger());
        }
    }

    /*{"data": [Post Object]}*/
    /**
     * @throws ErroredException
     */
    public function getPosts(): object
    {
        try {
            return json_decode(json_encode(
                (new Page(id: $this->PageID, api: $this->api))
                ->getPosts(self::PAGE_POST_FIELDS)?->getLastResponse()->getContent(),
                JSON_THROW_ON_ERROR
            ), false, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            Log::error('Facebook Posts: ');
            Log::error($e);
        }
        throw new ErroredException('an expected error occurred.');
    }

    /**
     * @throws ErroredException
     */
    public function deletePost(string $postId): bool
    {
        try {
            return (new PagePost($postId, api: $this->api))->deleteSelf()?->getContent()['success'];
        } catch (Exception $e) {
            Log::error($e);
        }

        throw new ErroredException('an expected error occurred.');
    }

    /**
     * post_impressions
     */
    public function getViews(string $postId): ?int
    {
        try {
            $response = json_decode(json_encode((new PagePost($postId, api: $this->api))->getInsights(params: [
                                                                                                               'metric' => 'post_impressions', //['post_impressions', 'post_impressions_unique'],
                                                                                                               'period' => InsightsResultPeriodValues::LIFETIME,
                                                                                                              ])?->getLastResponse()->getContent(), JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);

            return (int) $response->data[0]->values[0]->value;
        } catch (Exception $e) {
            Log::error('Facebook Insights: ');
            Log::error($e);
        }
        return null;
    }

    /**
     * return Post Object
     * @throws ErroredException
     */
    public function getPost(string $postId): object
    {
        try {
            return json_decode(json_encode((new PagePost($postId, api: $this->api))->getSelf(self::PAGE_POST_FIELDS)?->exportAllData(), JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            Log::error($e);
        }

        throw new ErroredException('an expected error occurred.');
    }

    /**
     *  COMMENT OBJECT
     * @throws ErroredException
     */
    public function getComments(string $postId, Carbon $since, string $commentID = null): object
    {
        $parameters = [
                       'order' => CommentOrderValues::REVERSE_CHRONOLOGICAL,
                       'since' => $since->format('U'),
                       'limit' => 100,
                      ];
        try {
            $comments = (is_null($commentID))
                ? (new PagePost($postId, api: $this->api))->getComments(self::COMMENT_FIELDS, $parameters)?->getLastResponse()->getContent()
                : (new Comment($commentID, api: $this->api))->getComments(self::COMMENT_FIELDS, $parameters)?->getLastResponse()->getContent();
            return json_decode(json_encode($comments, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            Log::error('Facebook Comments: ');
            Log::error($e);
        }

        throw new ErroredException('an expected error occurred.');
    }

    /**
     * COMMENT OBJECT
     * @throws ErroredException
     */
    public function createComment(string|int $parentID, string $message, string $parentType = 'post'): object
    {
        try {
            if ($parentType === 'post') {
                $comment = (new PagePost($parentID, api: $this->api))->createComment(self::COMMENT_FIELDS, ['message' => $message])?->exportAllData();
            } elseif ($parentType === 'comment') {
                $comment = (new Comment($parentID, api: $this->api))->createComment(self::COMMENT_FIELDS, ['message' => $message])?->exportAllData();
            } else {
                throw new ErroredException('Invalid parent type given');
            }
            return (object) json_decode(json_encode($comment, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
        } catch (ErroredException $e) {
            throw new ErroredException($e->getMessage());
        } catch (Exception $e) {
            Log::error('Facebook Comments: ');
            Log::error($e);
        }

        throw new ErroredException('an expected error occurred.');
    }
    public function syncComments(Social $social): self
    {
        if ($social->CommentsCount === 0) {
            return $this;
        }
        $actor = SystemHelper::user();
        $comments = $this->getComments($social->RemoteId, $social->CreatedOn);
        foreach ($comments->data as $comment) {
            $cmt = $social->comments()->where('RemoteId', $comment->id)->first();
            if ($cmt instanceof \App\Models\Communication\Comment) {
                $cmt->update([
                              'Notes'    => $comment->message,
                              'Response' => json_encode($comment),
                             ]);
                $cmtID = $cmt->Id;
            } else {
                $cmtID = $social->comments()->insertGetId([
                    'CommentType' => \App\Models\CRM\Social::getPrimaryKey(),
                                                           'CommentTypeID' => $social->Id,
                                                           'Notes'         => $comment->message,
                                                           'Response'      => json_encode($comment),
                                                           'Source'        => $social->Type->value,
                                                           'RemoteId'      => $comment->id,
                                                           'CreatedOn'     => Carbon::parse($comment->created_time)->timezone(config('app.timezone')),
                                                           'CreatedBy'     => $actor->Id,
                                                           'ModifiedOn'    => now(),
                                                           'ModifiedBy'    => $actor->Id,
                                                          ]);
            }
            if ($comment->comment_count > 0) {
                $toSave = collect([]);
                $commentComments = $this->getComments($social->RemoteId, $social->CreatedOn, $comment->id);
                foreach ($commentComments->data as $commentComment) {
                    $toSave->add([
                        'CommentType' => \App\Models\Communication\Comment::getPrimaryKey(),
                                  'CommentTypeID' => $cmtID,
                                  'Notes'         => $commentComment->message,
                                  'Response'      => json_encode($commentComment),
                                  'RemoteId'      => $commentComment->id,
                                  'Source'        => $social->Type->value,
                                  'CreatedOn'     => Carbon::parse($commentComment->created_time)->timezone(config('app.timezone')),
                                  'CreatedBy'     => $actor->Id,
                                  'ModifiedOn'    => now(),
                                  'ModifiedBy'    => $actor->Id,
                                 ]);
                }

                if ($toSave->isNotEmpty()) {
                    DB::table('t_Comments')->insert($toSave->toArray());
                }
            }
        }

        return $this;
    }

    public function publish(Social $social): bool
    {
        if ($social->Type->value !== IntegrationsEnum::Facebook->value) {
            return false;
        }

        if (!is_null($social->Published_at)) {
            return true;
        }

        try {
            $Remote = $this->_createPost($social->Content, $social->images);
        } catch (Exception | ErroredException) {
            return false;
        }

        $social->update([
                         'Published_at' => Carbon::now(),
                         'RemoteId'     => $Remote->id,
                         'Response'     => json_encode($Remote),
                        ]);

        return true;
    }

    /**
     * Post Object
     * @throws ErroredException
     */
    protected function _createPost(string $message, Collection $medias = null): object
    {
        $id = 0;
        $images = collect();
        if ($medias instanceof Collection && $medias->count() > 0) {
            foreach ($medias as $media) {
                if ($media instanceof Image) {
                    $images = $images->merge([
                                              "attached_media[" . $id . "]" => json_encode(['media_fbid' => $this->_uploadMedia($media)]),
                                             ]);
                    $id++;
                }
            }
        }

        $params = $images->merge([
                                  'message'   => $message,
                                  'published' => true,
            //'scheduled_publish_time' => $publish_at->format('U')
                                 ]);

        try {
            return json_decode(json_encode((new Page($this->PageID))->createFeed(self::PAGE_POST_FIELDS, $params->toArray())?->exportAllData(), JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            Log::error('Post Facebook: ');
            Log::error($e);
        }

        throw new ErroredException('an expected error occurred.');
    }

    /**
     * @throws ErroredException
     */
    protected function _uploadMedia(Image $image): string
    {
        try {/*file_get_contents($imagePath), basename($imagePath)*/
            $response = Http::attach(
                'source',
                base64_decode($image->Image),
                ($image->Name) ?? 'filename',
                ['Content-Type' => $image->MIMEType]
            )->post("https://graph.facebook.com/v21.0/" . $this->PageID . "/photos", [
                                                                                      'access_token' => $this->pageToken,
                                                                                      'published'    => false,
                                                                                     ]);

            if ($response->successful()) {
                return $response->object()->id;
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        throw new ErroredException('Uploading Media failed');
    }

    /**
     * { "id": "", "name": "","website":""}
     * @throws ErroredException
     */
    public function getPage(): object
    {
        try {
            return json_decode(json_encode((new Page(id: $this->PageID))
                ->getSelf(self::PAGE_FIELDS)?->exportAllData(), JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            Log::error($e);
        }

        throw new ErroredException('an expected error occurred.');
    }

    public function userPermissions(): array
    {
        $user = new User(id: $this->_UserID(), api: $this->api);
        $required = self::PAGE_PERMISSIONS;
        $permissions = collect($user->getPermissions()?->getLastResponse()->getContent()['data']);
        foreach ($permissions as $permission) {
            if ($permission['status'] === 'granted') {
                $required = array_diff($required, [$permission['permission']]);
            }
        }

        return $required;
    }
    public function updateToken(): void
    {
        if (!$this->credential instanceof APICredential) {
            Log::error('Cannot update credentials when initializing.');
        }

        $Config = $this->credential?->Configuration;

        //check if expiring less than 21 days.
        try {
            if (is_numeric($Config?->page_token_expires_at)) {
                $configDate = Carbon::createFromFormat('U', $Config?->page_token_expires_at);
                if (!$configDate instanceof Carbon) {
                    throw new Exception('Invalid page_token_expires_at given');
                }
            } else {
                throw new Exception('Invalid page_token_expires_at given');
            }
        } catch (Exception | Throwable $e) {
            SystemHelper::notifyAdmin('Could not update facebook token, cannot ascertain expire date');
            Log::error($e);
            return;
        }

        if (Carbon::now()->addDays(self::RENEW_DAYS_BEFORE_EXPIRE)->lessThan($configDate)) {
            return;
        }

        try {
            $tokenResponse = $this->refreshToken();
        } catch (ErroredException $e) {
            SystemHelper::notifyAdmin('Could not update facebook token, Could not fetch new token');
            Log::error($e);
            return;
        }

        $this->credential->update([
                                   'Configuration' => [
                                                       'page_id'               => $this->PageID,
                                                       'app_id'                => $this->AppId,
                                                       'app_secret'            => $this->appSecret,
                                                       'page_token'            => $tokenResponse->access_token,
                                                       'page_token_expires_at' => bcadd($tokenResponse->expires_in, now()->format('U')),
                                                       'page_name'             => $Config->page_name,
                                                      ],
                                  ]);
    }

    /**
     * @throws ErroredException
     */
    private function _UserID(): string
    {
        $response = Http::get('https://graph.facebook.com/v21.0/me?fields=id&access_token=' . $this->pageToken);
        try {
            if ($response->successful()) {
                return (json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR)['id']) ?? '';
            }
        } catch (Exception $e) {
            Log::error($e);
        }

        throw new ErroredException('token given could be invalid, kindly check.');
    }

    /**
     * { "access_token": "", "token_type": "bearer", "expires_in": (int) (timestamp + this to get date) }
     *
     * @throws ErroredException
     */
    public function refreshToken(): object
    {
        $response = Http::get('https://graph.facebook.com/oauth/access_token', [
                                                                                'grant_type'        => 'fb_exchange_token',
                                                                                'client_id'         => $this->AppId,
                                                                                'client_secret'     => $this->appSecret,
                                                                                'fb_exchange_token' => $this->pageToken,
                                                                               ]);

        try {
            if ($response->successful()) {
                return (object) json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            }
        } catch (JsonException $e) {
            Log::error($e);
        }

        throw new ErroredException('token given could be invalid, kindly check.');
    }

    /**
     * "has_review" => true
     * "has_rating" => false
     * "created_time" => "2022-04-22T16:04:55+0000"
     * "recommendation_type" => "positive"
     * "review_text" => "Excellent service delivery. Highly recommend them"
     * @return Collection
     */
    public function getReviews(): Collection
    {
        try {
            return collect((new Page(id: $this->PageID))->getRatings(['rating', 'has_review', 'has_rating', 'created_time', 'recommendation_type', 'review_text', 'reviewer'])?->getLastResponse()->getContent()['data']);
        } catch (Exception $e) {
            Log::error($e);
        }
        return collect([]);
    }
}

/**
 * *** POST OBJECT **
 *
 * {"id": ""
 * +"is_hidden": false
 * +"is_eligible_for_promotion": true
 * +"created_time": "2024-11-30T07:00:08+0000"
 * +"updated_time": "2024-11-30T07:00:08+0000"
 * +"message": ""
 * +"picture": ""
 * +"full_picture": ""
 * +"is_published": true
 * +"likes": { +"summary": {+"total_count": 2 }}
 * +"comments": {+"summary": {+"total_count": 0} }
 * }*/

/**
 *  *** Comment Object
 *
 * {
 *  +"id": "950699083781124_8683221775140779"
 *  +"can_comment": true
 *  +"can_hide": true
 *  +"can_like": true
 *  +"created_time": "2025-01-09T07:08:02+0000"
 *  +"like_count": 0
 *  +"comment_count": 0
 *  +"message": "This is great"
 *  +"from": {"name": "Mureithi Maina", "id": "8960049930748677"}
 *  +"can_remove": true
 *  +"is_hidden": false
 *  }
 */
