<?php

namespace App\Http\Controllers\ThirdParty\API;

use App\Enums\TicketPriorityEnum;
use App\Enums\TicketSourceEnum;
use App\Enums\TicketStatusEnum;
use App\Helpers\SystemHelper;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Communication\Comment;
use App\Models\Core\Approval\CodeDetail;
use App\Models\CRM\Ticket;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Services\CRMEmailService;
use App\Services\StaticListsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class HelpTicketController extends Controller
{
    public function categories(): JsonResponse
    {
        $categories = StaticListsService::getRawList(StaticListsService::TicketCategories)
            ->get()
            ->map(fn (CodeDetail $category) => [
                'id' => $category->ID,
                'name' => $category->Description,
                'value' => $category->Value,
                'display_order' => $category->DisplayOrder,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = max(1, min((int) $request->integer('per_page', 10), 50));

        $tickets = $this->ticketQuery($user)
            ->with(['category', 'status'])
            ->orderByDesc('CreatedOn')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'tickets' => collect($tickets->items())->map(fn (Ticket $ticket) => $this->ticketPayload($ticket, false)),
                'pagination' => [
                    'current_page' => $tickets->currentPage(),
                    'per_page' => $tickets->perPage(),
                    'total' => $tickets->total(),
                    'last_page' => $tickets->lastPage(),
                ],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('t_CodeDetails', 'ID')->where(fn ($q) => $q->where('CodeID', StaticListsService::TicketCategories)->where('IsActive', true)),
            ],
            'priority' => ['nullable', 'string', Rule::in(['normal', 'low', 'urgent'])],
            'mentions' => ['nullable', 'array', 'max:25'],
            'mentions.*' => ['integer'],
        ]);

        $user = $request->user();
        $actor = SystemHelper::user();
        $category = $this->resolveCategory($validated['category_id'] ?? null);

        if (! $category) {
            return response()->json([
                'success' => false,
                'message' => 'Support categories are not configured. Please contact support.',
            ], 422);
        }

        $priority = $this->priorityFromInput($validated['priority'] ?? null);
        $mentions = $this->resolveMentions($user, $validated['mentions'] ?? []);

        $ticket = DB::transaction(function () use ($validated, $user, $actor, $category, $priority, $mentions) {
            $ticket = new Ticket();
            $ticket->fill([
                'TicketID' => $this->generateTicketId(),
                'Title' => $validated['subject'],
                'CategoryID' => $category->ID,
                'Notes' => $validated['message'],
                'Party' => $this->partyType($user),
                'PartyID' => $this->partyId($user),
                'Source' => TicketSourceEnum::Website->value,
                'SourceID' => '0',
                'StatusId' => TicketStatusEnum::Active->codeDetail()->ID,
                'Priority' => $priority->value,
                'Owner' => User::getPrimaryKey(),
                'OwnerID' => $actor->Id,
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
            ])->save();

            $this->storeComment($ticket, $validated['message'], $user, $actor, $mentions);

            return $ticket->refresh()->load(['category', 'status']);
        });

        $this->sendCreateEmails($ticket, $user, $validated['message'], $mentions);

        return response()->json([
            'success' => true,
            'data' => $this->ticketPayload($ticket, true, $user),
            'message' => 'Support ticket created successfully.',
        ], 201);
    }

    public function show(Request $request, string $ticketId): JsonResponse
    {
        $user = $request->user();
        $ticket = $this->ticketQuery($user)
            ->where('TicketID', $ticketId)
            ->with([
                'category',
                'status',
                'comments' => fn ($q) => $q->orderBy('CreatedOn')->with('creator'),
            ])
            ->first();

        if (! $ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->ticketPayload($ticket, true, $user),
        ]);
    }

    public function reply(Request $request, string $ticketId): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:10000'],
            'mentions' => ['nullable', 'array', 'max:25'],
            'mentions.*' => ['integer'],
        ]);

        $user = $request->user();
        $ticket = $this->ticketQuery($user)->where('TicketID', $ticketId)->with('status')->first();

        if (! $ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found.',
            ], 404);
        }

        if (in_array($ticket->status?->Value, [TicketStatusEnum::Resolved->value, TicketStatusEnum::Cancelled->value], true)) {
            return response()->json([
                'success' => false,
                'message' => 'This ticket is closed and cannot accept replies.',
            ], 422);
        }

        $actor = SystemHelper::user();
        $mentions = $this->resolveMentions($user, $validated['mentions'] ?? []);

        $comment = DB::transaction(function () use ($ticket, $validated, $user, $actor, $mentions) {
            return $this->storeComment($ticket, $validated['message'], $user, $actor, $mentions);
        });

        $this->sendReplyEmails($ticket, $user, $validated['message'], $mentions);

        return response()->json([
            'success' => true,
            'data' => [
                'ticket_id' => $ticket->TicketID,
                'message' => $this->messagePayload($comment, $user),
            ],
            'message' => 'Reply sent successfully.',
        ]);
    }

    public function mentions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'query' => ['nullable', 'string', 'max:80'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:25'],
        ]);

        /** @var ThirdPartyUser $user */
        $user = $request->user();
        $query = trim((string) ($validated['query'] ?? $validated['q'] ?? ''));
        $limit = (int) ($validated['limit'] ?? 15);

        $users = $this->resolveMentionableUsers($user, $query !== '' ? $query : null, $limit)
            ->reject(fn (ThirdPartyUser $candidate) => (int) $candidate->Id === (int) $user->Id)
            ->map(fn (ThirdPartyUser $candidate) => $this->mentionPayload($candidate))
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'mentions' => $users,
            ],
        ]);
    }

    private function ticketQuery(ThirdPartyUser $user): Builder
    {
        return Ticket::query()
            ->where('Source', TicketSourceEnum::Website->value)
            ->where(function ($q) use ($user) {
                if ($user->ThirdPartyId) {
                    $q->where(function ($sub) use ($user) {
                        $sub->where('Party', 'ThirdParty')
                            ->where('PartyID', $user->ThirdPartyId);
                    })->orWhere(function ($sub) use ($user) {
                        $sub->where('Party', 'ThirdPartyUser')
                            ->where('PartyID', $user->Id);
                    });

                    return;
                }

                $q->where('Party', 'ThirdPartyUser')
                    ->where('PartyID', $user->Id);
            });
    }

    private function ticketPayload(Ticket $ticket, bool $includeMessages, ?ThirdPartyUser $viewer = null): array
    {
        $payload = [
            'id' => $ticket->TicketID,
            'subject' => $ticket->Title,
            'status' => [
                'code' => $ticket->status?->Value,
                'label' => $ticket->status?->Description,
            ],
            'priority' => [
                'code' => $ticket->Priority?->value,
                'label' => $ticket->Priority?->name,
            ],
            'category' => [
                'id' => $ticket->category?->ID,
                'name' => $ticket->category?->Description,
            ],
            'created_at' => $ticket->CreatedOn,
            'updated_at' => $ticket->ModifiedOn,
        ];

        if (! $includeMessages) {
            return $payload;
        }

        $messages = $ticket->relationLoaded('comments')
            ? $ticket->comments
            : $ticket->comments()->orderBy('CreatedOn')->with('creator')->get();

        $payload['messages'] = $messages->map(fn (Comment $comment) => $this->messagePayload($comment, $viewer));

        return $payload;
    }

    private function messagePayload(Comment $comment, ?ThirdPartyUser $user = null): array
    {
        $meta = $this->commentMeta($comment);
        $authorType = $meta['author_type'] ?? 'System';
        $authorId = isset($meta['author_id']) ? (string) $meta['author_id'] : null;

        return [
            'id' => $comment->Id,
            'body' => $comment->Notes,
            'author' => [
                'type' => $authorType,
                'id' => $authorId,
                'name' => $meta['author_name'] ?? $comment->creator?->Name ?? 'System',
                'email' => $meta['author_email'] ?? null,
            ],
            'is_mine' => $user
                ? ($authorType === 'ThirdPartyUser' && $authorId === (string) $user->Id)
                : ($authorType === 'ThirdPartyUser'),
            'mentions' => collect($meta['mentions'] ?? [])->map(function ($mention) {
                $item = is_object($mention) ? (array) $mention : (is_array($mention) ? $mention : []);

                return [
                    'id' => isset($item['id']) ? (int) $item['id'] : null,
                    'name' => $item['name'] ?? null,
                    'email' => $item['email'] ?? null,
                ];
            })->filter(fn ($mention) => ! is_null($mention['id']))->values()->all(),
            'created_at' => $comment->CreatedOn,
        ];
    }

    private function storeComment(Ticket $ticket, string $message, ThirdPartyUser $user, User $actor, Collection $mentions): Comment
    {
        $comment = new Comment();
        $comment->fill([
            'CommentType' => Ticket::getPrimaryKey(),
            'CommentTypeID' => $ticket->Id,
            'Notes' => $message,
            'Response' => [
                'channel' => 'portal',
                'author_type' => 'ThirdPartyUser',
                'author_id' => (string) $user->Id,
                'author_name' => $user->fullName,
                'author_email' => $user->Email,
                'mentions' => $mentions->map(fn (ThirdPartyUser $mentionedUser) => $this->mentionPayload($mentionedUser))->values()->all(),
            ],
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ])->save();

        return $comment->refresh()->load('creator');
    }

    private function sendCreateEmails(Ticket $ticket, ThirdPartyUser $user, string $message, Collection $mentions): void
    {
        $ticketId = $ticket->TicketID;
        $subject = "Support ticket {$ticketId} created";
        $body = "<p>Hello {$user->fullName},</p>
            <p>Your support ticket <strong>{$ticketId}</strong> was created successfully.</p>
            <p><strong>Subject:</strong> {$ticket->Title}</p>
            <p><strong>Message:</strong><br>" . e($message) . '</p>
            <p>We will update you as soon as possible.</p>';

        $this->sendEmailSafely(
            $user->Email,
            $user->fullName,
            $subject,
            $body,
            $this->partyType($user),
            (string) $this->partyId($user),
            $this->baseNotificationMeta($ticket, 'ticket_created', "Ticket {$ticketId} created")
        );

        $supportSubject = "[Portal Help] New ticket {$ticketId}";
        $supportBody = "<p>New portal help ticket submitted.</p>
            <p><strong>Ticket:</strong> {$ticketId}</p>
            <p><strong>Company:</strong> " . e($user->thirdParty?->ThirdPartyName ?? 'N/A') . "</p>
            <p><strong>User:</strong> " . e($user->fullName) . " ({$user->Email})</p>
            <p><strong>Subject:</strong> " . e($ticket->Title) . "</p>
            <p><strong>Message:</strong><br>" . e($message) . '</p>';

        $this->sendEmailSafely(config('org.email'), config('org.name', 'Support Team'), $supportSubject, $supportBody, null, null, [
            'event' => 'ticket_received',
            'ticket_id' => $ticketId,
            'source' => 'portal_help_ticket',
        ]);

        $this->notifyMentionedUsers($ticket, $user, $message, $mentions);
    }

    private function sendReplyEmails(Ticket $ticket, ThirdPartyUser $user, string $message, Collection $mentions): void
    {
        $ticketId = $ticket->TicketID;
        $subject = "Support ticket {$ticketId} updated";
        $body = "<p>Hello {$user->fullName},</p>
            <p>We received your update for ticket <strong>{$ticketId}</strong>.</p>
            <p><strong>Message:</strong><br>" . e($message) . '</p>
            <p>Our support team will review and respond.</p>';

        $this->sendEmailSafely(
            $user->Email,
            $user->fullName,
            $subject,
            $body,
            $this->partyType($user),
            (string) $this->partyId($user),
            $this->baseNotificationMeta($ticket, 'ticket_reply_sent', "Reply sent on {$ticketId}")
        );

        $supportSubject = "[Portal Help] Reply on ticket {$ticketId}";
        $supportBody = "<p>New portal reply received.</p>
            <p><strong>Ticket:</strong> {$ticketId}</p>
            <p><strong>User:</strong> " . e($user->fullName) . " ({$user->Email})</p>
            <p><strong>Reply:</strong><br>" . e($message) . '</p>';

        $this->sendEmailSafely(config('org.email'), config('org.name', 'Support Team'), $supportSubject, $supportBody, null, null, [
            'event' => 'ticket_reply_received',
            'ticket_id' => $ticketId,
            'source' => 'portal_help_ticket',
        ]);

        $this->notifyMentionedUsers($ticket, $user, $message, $mentions);
    }

    private function sendEmailSafely(
        ?string $email,
        string $name,
        string $subject,
        string $body,
        ?string $party = null,
        ?string $partyId = null,
        ?array $extra = null
    ): void {
        if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            $service = CRMEmailService::createRaw(
                SystemHelper::user(),
                $subject,
                $body,
                [[$name => $email]],
                $party,
                $partyId
            );

            if (is_array($extra) && ! empty($extra)) {
                $existingExtra = $this->normalizeExtra($service->crmEmail->Extra);
                $service->crmEmail->forceFill([
                    'Extra' => (object) array_merge($existingExtra, $extra),
                ])->save();
            }

            $replyToEmail = config('support.queue_email', config('org.email'));
            $replyToName = config('support.queue_name', config('org.name'));
            if (is_string($replyToEmail) && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
                $service->setReplyTo($replyToEmail, (string) $replyToName);
            }

            $service->send(true);
        } catch (Throwable $e) {
            Log::warning('Unable to send help email.', [
                'email' => $email,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function resolveCategory(?int $categoryId): ?CodeDetail
    {
        if ($categoryId) {
            return StaticListsService::getRawList(StaticListsService::TicketCategories)
                ->where('ID', $categoryId)
                ->first();
        }

        return StaticListsService::getRawList(StaticListsService::TicketCategories)->first();
    }

    private function priorityFromInput(?string $priority): TicketPriorityEnum
    {
        return match (Str::lower((string) $priority)) {
            'urgent' => TicketPriorityEnum::Urgent,
            'low' => TicketPriorityEnum::Low,
            default => TicketPriorityEnum::Normal,
        };
    }

    private function generateTicketId(): string
    {
        $number = Ticket::withTrashed()->count();

        do {
            $number++;
            $ticketId = Str::slug('T' . Str::padLeft((string) $number, 4, '0'));
        } while (Ticket::withTrashed()->where('TicketID', $ticketId)->exists());

        return Str::upper($ticketId);
    }

    private function commentMeta(Comment $comment): array
    {
        if (is_object($comment->Response)) {
            return (array) $comment->Response;
        }

        if (is_array($comment->Response)) {
            return $comment->Response;
        }

        return [];
    }

    private function resolveMentions(ThirdPartyUser $user, array $mentionIds): Collection
    {
        $ids = collect($mentionIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        $query = ThirdPartyUser::query()
            ->whereIn('Id', $ids->all())
            ->where('IsActive', true)
            ->whereNotNull('Email');

        if ($user->ThirdPartyId) {
            $query->where('ThirdPartyId', $user->ThirdPartyId);
        } else {
            $query->where('Id', $user->Id);
        }

        $mentionedUsers = $query->get();
        $resolvedIds = $mentionedUsers->pluck('Id')->map(fn ($id) => (int) $id)->all();
        $missingIds = array_values(array_diff($ids->all(), $resolvedIds));

        if (! empty($missingIds)) {
            throw ValidationException::withMessages([
                'mentions' => ['One or more mentioned users are invalid or unavailable for your organization.'],
            ]);
        }

        return $mentionedUsers;
    }

    private function resolveMentionableUsers(ThirdPartyUser $user, ?string $query = null, int $limit = 15): Collection
    {
        $search = trim((string) $query);

        $builder = ThirdPartyUser::query()
            ->where('IsActive', true)
            ->whereNotNull('Email');

        if ($user->ThirdPartyId) {
            $builder->where('ThirdPartyId', $user->ThirdPartyId);
        } else {
            $builder->where('Id', $user->Id);
        }

        if ($search !== '') {
            $builder->where(function ($q) use ($search) {
                $q->where('FirstName', 'like', "%{$search}%")
                    ->orWhere('LastName', 'like', "%{$search}%")
                    ->orWhere('Email', 'like', "%{$search}%");
            });
        }

        return $builder
            ->orderBy('FirstName')
            ->orderBy('LastName')
            ->limit(max(1, min($limit, 25)))
            ->get(['Id', 'FirstName', 'LastName', 'Email', 'ThirdPartyId']);
    }

    private function mentionPayload(ThirdPartyUser $mentionedUser): array
    {
        return [
            'id' => (int) $mentionedUser->Id,
            'name' => $mentionedUser->fullName,
            'email' => $mentionedUser->Email,
        ];
    }

    private function notifyMentionedUsers(Ticket $ticket, ThirdPartyUser $sender, string $message, Collection $mentions): void
    {
        if ($mentions->isEmpty()) {
            return;
        }

        $ticketId = $ticket->TicketID;
        $subject = "You were mentioned on support ticket {$ticketId}";

        foreach ($mentions as $mentionedUser) {
            if (! $mentionedUser instanceof ThirdPartyUser) {
                continue;
            }

            if ((int) $mentionedUser->Id === (int) $sender->Id) {
                continue;
            }

            $body = "<p>Hello " . e($mentionedUser->fullName) . ",</p>
                <p>" . e($sender->fullName) . " mentioned you in support ticket <strong>{$ticketId}</strong>.</p>
                <p><strong>Subject:</strong> " . e($ticket->Title) . "</p>
                <p><strong>Message:</strong><br>" . e($message) . '</p>
                <p>Please log in to the portal to continue the conversation.</p>';

            $this->sendEmailSafely(
                $mentionedUser->Email,
                $mentionedUser->fullName,
                $subject,
                $body,
                'ThirdPartyUser',
                (string) $mentionedUser->Id,
                array_merge(
                    $this->baseNotificationMeta($ticket, 'ticket_mentioned', "Mentioned on ticket {$ticketId}"),
                    [
                        'mentioned_by' => [
                            'id' => (int) $sender->Id,
                            'name' => $sender->fullName,
                            'email' => $sender->Email,
                        ],
                    ]
                )
            );
        }
    }

    private function baseNotificationMeta(Ticket $ticket, string $event, string $title): array
    {
        return [
            'event' => $event,
            'source' => 'portal_help_ticket',
            'ticket_id' => $ticket->TicketID,
            'title' => $title,
            'link' => $this->ticketPortalLink($ticket),
        ];
    }

    private function ticketPortalLink(Ticket $ticket): string
    {
        $baseUrl = rtrim((string) (config('app.frontend_url') ?: config('app.url')), '/');

        return "{$baseUrl}/dashboard/help/tickets/{$ticket->TicketID}";
    }

    private function normalizeExtra($extra): array
    {
        if (is_array($extra)) {
            return $extra;
        }

        if (is_object($extra)) {
            return (array) $extra;
        }

        if (is_string($extra) && $extra !== '') {
            $decoded = json_decode($extra, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function partyType(ThirdPartyUser $user): string
    {
        return $user->ThirdPartyId ? 'ThirdParty' : 'ThirdPartyUser';
    }

    private function partyId(ThirdPartyUser $user): int
    {
        return $user->ThirdPartyId ?: $user->Id;
    }
}
