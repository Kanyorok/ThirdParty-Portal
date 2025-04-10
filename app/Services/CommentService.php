<?php

namespace App\Services;

use App\Enums\Core\IntegrationsEnum;
use App\Helpers\SystemHelper;
use App\Models\Comment;
use App\Models\ProductDevelopment;
use App\Models\Social;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Number;

class CommentService
{
    public function __construct(public Comment $comment)
    {
    }

    public function commenter(): array
    {
        if (($this->comment->creator->UserID === SystemHelper::ID) && (in_array($this->comment->CommentType, [Social::getPrimaryKey(), Comment::getPrimaryKey()], true))) {

            if ($this->comment->Source === IntegrationsEnum::Facebook->value) {
                return [
                    'name' => ($this->comment->Response?->from?->name) ?? 'facebook user',
                    'avatar' => '<img src="https://deberepi.sirv.com/Social/facebook.png" width="36" height="36" class="rounded-circle me-2" alt="No Image"/>'
                ];
            }

            if ($this->comment->Source === IntegrationsEnum::Twitter->value) {
                return [
                    'name' => ($this->comment->Response?->user?->name) ?? 'twitter user',
                    'avatar' => '<img src="https://deberepi.sirv.com/Social/twitter.png" width="36" height="36" class="rounded-circle me-2" alt="No Image"/>'
                ];
            }

            return [
                'name' => 'unknown user',
                'avatar' => '<img src="https://placehold.co/36/green/FFF?text=none" width="36" height="36" class="rounded-circle me-2" alt="No Image"/>'
            ];
        }


        return [
            'avatar' => $this->comment->creator->getImage(' width="36" height="36" class="rounded-circle me-2"'),
            'name' => $this->comment->creator->Name . ' (' . $this->comment->creator->UserID . ')'
        ];
    }
    public static function forTicket(Ticket $ticket, string $description, User $actor): CommentService
    {
        return self::_create(Ticket::getPrimaryKey(), $ticket->Id, $description, $actor);
    }

    public static function forSocial(Social $social, string $description, User $actor, object $response, string $remoteId): CommentService
    {
        return self::_create(Social::getPrimaryKey(), $social->Id, $description, $actor, $response, $remoteId, $social->Type);
    }

    public static function forComment(Comment $comment, string $description, User $actor, object $response = null, string $remoteId = null, IntegrationsEnum $source = null): CommentService
    {
        return self::_create(Comment::getPrimaryKey(), $comment->Id, $description, $actor, $response, $remoteId, $source);
    }

    public static function forProductDev(ProductDevelopment $product, string $description, User $actor): CommentService
    {
        return self::_create(ProductDevelopment::getPrimaryKey(), $product->Id, $description, $actor);
    }

    private static function _create(string $type, string $typeId, string $description, User $actor, object $response = null, string $remoteId = null, IntegrationsEnum $source = null): CommentService
    {
        $comment = new Comment();
        $comment->fill([
            'CommentType' => $type,
            'CommentTypeID' => $typeId,
            'Notes' => $description,
            'Response' => $response,
            'RemoteId' => $remoteId,
            'Source' => $source?->value,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ])->save();

        return (new CommentService($comment->refresh()));
    }

    public function notify(): static
    {
        return $this;
    }

    public function trashable(User $actor): bool
    {
        if ($this->comment->CommentType === Social::getPrimaryKey()) {
            return false;//todo fix with the social permission and upstream permission
        }
        if ($this->comment->trashed()) {
            return false;
        }

        if ($this->comment->CreatedBy !== $actor->Id) {
            return false;
        }

        if (!$this->comment->CreatedOn instanceof Carbon) {
            return false;
        }

        return (now()->subDays(3)->lt($this->comment->CreatedOn));

    }

    public function extras(): array
    {
        $data = ['likes' => ['numeric' => 0, 'string' => ''], 'comments' => ['numeric' => 0, 'string' => '']];
        $likes = (is_int($this->comment->Response?->like_count)) ? $this->comment->Response?->like_count : 0;
        $comments = $this->comment->comments()->count();//(is_int($this->comment->Response?->comment_count)) ? $this->comment->Response?->comment_count : 0;

        data_set($data, 'likes.numeric', $likes);
        data_set($data, 'likes.string', Number::abbreviate($likes, ($likes > 999) ? 1 : 0));

        data_set($data, 'comments.numeric', $comments);
        data_set($data, 'comments.string', Number::abbreviate($comments, ($comments > 999) ? 1 : 0));


        return $data;
    }
}
