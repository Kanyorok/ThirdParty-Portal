<?php

namespace App\Services;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\IntegrationsEnum;
use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Communication\Comment;
use App\Models\CRM\Social;
use App\Models\DMS\Image;
use App\Services\ThirdParty\FacebookService;
use App\Services\ThirdParty\TwitterService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SocialMediaService
{
    public function __construct(public Social $social)
    {
    }

    /**
     * @param object $postObject <Facebook Post Object>
     * @param User $actor
     * @return void
     */
    public static function createFromFacebook(object $postObject, User $actor): void
    {

        $fbService = new FacebookService();
        $service = self::createFromPublished(
            $postObject->id,
            IntegrationsEnum::Facebook,
            ($postObject->message) ?? "",
            $fbService->getViews($postObject->id) ?? 0,
            $postObject->likes->summary->total_count,
            $postObject->comments->summary->total_count,
            Carbon::parse($postObject->created_time)->timezone(config('app.timezone')),
            Carbon::parse($postObject->updated_time)->timezone(config('app.timezone')),
            json_encode($postObject),
            $actor
        );

        if (filter_var($postObject->full_picture, FILTER_VALIDATE_URL) !== false) {
            $service->mediaFromUrl($postObject->full_picture, $actor, ExtensionsEnum::Jpeg->getMimeType());
        }

        $fbService->syncComments($service->social);
    }

    protected static function createFromPublished(
        string $Id,
        IntegrationsEnum $integration,
        string $content,
        int $views,
        int $likes,
        int $comments,
        Carbon $created,
        Carbon $updated,
        $response,
        User $actor
    ): self {
        $id = Social::insertGetId([
                                   'SocialID'      => self::_ID(),
                                   'RemoteId'      => $Id,
                                   'Type'          => $integration->value,
                                   'Content'       => $content,
                                   'LikesCount'    => $likes,
                                   'CommentsCount' => $comments,
                                   'ViewsCount'    => $views,
                                   'Published_at'  => $updated,
                                   'Response'      => $response,
                                   'CreatedOn'     => $created,
                                   'CreatedBy'     => $actor->Id,
                                   'ModifiedOn'    => $updated,
                                   'ModifiedBy'    => $actor->Id,
                                  ]);

        return new self(Social::query()->findOrFail($id));
    }

    protected static function _ID(): string
    {
        $number = Social::query()->withTrashed()->count();
        do {
            $number++;
            $slug = Str::slug('Social' . Str::padLeft(($number), 4, '0'));
        } while (Social::where('SocialID', $slug)->withTrashed()->exists());

        return $slug;
    }

    public function mediaFromUrl(string $url, User $actor, string $MimeType): static
    {
        return $this->addImage(ImageService::createURL($url, Social::getPrimaryKey(), $this->social->Id, $actor, $MimeType)->image, $actor);
    }

    public function addImage(Image $image, User $actor): static
    {
        $this->social->images()->attach($image->ImageID, ['CreatedBy' => $actor->Id, 'ModifiedBy' => $actor->Id]);
        return $this;
    }

    public static function createFromTwitter(object $postObject, User $actor): void
    {
        $post = $postObject['tweet'];
        $service = self::createFromPublished(
            $post->id,
            IntegrationsEnum::Twitter,
            ($post->text) ?? "",
            $post->public_metrics->impression_count,
            $post->public_metrics->like_count,
            $post->public_metrics->reply_count,
            Carbon::parse($post->created_at)->timezone(config('app.timezone')),
            Carbon::parse($post->created_at)->timezone(config('app.timezone')),
            json_encode($postObject),
            $actor
        );

        $medias = $postObject['media'];
        foreach ($medias as $media) {
            if ($media->type === "photo") {
                $service->mediaFromUrl($media->url, $actor, ExtensionsEnum::Jpeg->getMimeType());
                break;
            }
            if ($media->type === "video") {
                //save image preview preview_image_url
                $service->mediaFromUrl($media->preview_image_url, $actor, ExtensionsEnum::Jpeg->getMimeType());

                //save
                foreach ($media->variants as $variant) {
                    if (Str::contains($variant->content_type, "mp4") && (filter_var($variant->url, FILTER_VALIDATE_URL) !== false)) {
                        $service->mediaFromUrl($variant->url, $actor, $variant->content_type);
                        break;
                    }
                }
            }
        }
    }

    /**
     * @throws ErroredException
     */
    public static function create(IntegrationsEnum $socialType, string $content, Carbon $scheduled_at, User $actor): SocialMediaService
    {
        if (!$socialType->isSocial()) {
            throw new ErroredException("Social media type " . $socialType->name . " not allowed");
        }
        $social = new Social();
        $social->fill([
                       'SocialID'      => self::_ID(),
                       'RemoteId'      => '',
                       'Type'          => $socialType->value,
                       'Content'       => $content,
                       'LikesCount'    => 0,
                       'CommentsCount' => 0,
                       'ViewsCount'    => 0,
                       'Scheduled_at'  => $scheduled_at,
                       'CreatedBy'     => $actor->Id,
                       'ModifiedBy'    => $actor->Id,
                      ])->save();

        return new self($social);
    }

    /**
     * @throws ErroredException
     */
    public function comment(string $description, User $actor, Comment $comment = null): Comment
    {
        if ($this->social->Type->value === IntegrationsEnum::Facebook->value) {
            $service = new FacebookService();

            if ($comment instanceof Comment) {
                if ($comment->Source !== IntegrationsEnum::Facebook->value) {
                    throw new ErroredException("Comment source is not Facebook comment");
                }
                $newComment = $service->createComment($comment->RemoteId, $description, 'comment');
                return CommentService::forComment($comment, $description, $actor, $newComment, $newComment->id, IntegrationsEnum::Facebook)->comment;
            }

            $newComment = $service->createComment($this->social->RemoteId, $description);

            return CommentService::forSocial($this->social, $description, $actor, $newComment, $newComment->id)->comment;
        }
        throw new ErroredException("Not implemented");
    }

    public function publish(): bool
    {
        if (!is_null($this->social->Published_at)) {
            return true;
        }

        if ($this->social->Type->value === IntegrationsEnum::Facebook->value) {
            return (new FacebookService())->publish($this->social);
        }
        if ($this->social->Type->value === IntegrationsEnum::Twitter->value) {
            return (new TwitterService())->publish($this->social);
        }

        return false;
    }
}
