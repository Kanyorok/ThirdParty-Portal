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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
            'message' => ['required', 'string'],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('t_CodeDetails', 'ID')->where(fn ($q) => $q->where('CodeID', StaticListsService::TicketCategories)->where('IsActive', true)),
            ],
            'priority' => ['nullable', 'string', Rule::in(['normal', 'low', 'urgent'])],
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

        $ticket = DB::transaction(function () use ($validated, $user, $actor, $category, $priority) {
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

            $this->storeComment($ticket, $validated['message'], $user, $actor);

            return $ticket->refresh()->load(['category', 'status']);
        });

        $this->sendCreateEmails($ticket, $user, $validated['message']);

        return response()->json([
            'success' => true,
            'data' => $this->ticketPayload($ticket, true),
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
            'data' => $this->ticketPayload($ticket, true),
        ]);
    }

    public function reply(Request $request, string $ticketId): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string'],
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
        $comment = DB::transaction(function () use ($ticket, $validated, $user, $actor) {
            return $this->storeComment($ticket, $validated['message'], $user, $actor);
        });

        $this->sendReplyEmails($ticket, $user, $validated['message']);

        return response()->json([
            'success' => true,
            'data' => [
                'ticket_id' => $ticket->TicketID,
                'message' => $this->messagePayload($comment, $user),
            ],
            'message' => 'Reply sent successfully.',
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

    private function ticketPayload(Ticket $ticket, bool $includeMessages): array
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

        $payload['messages'] = $messages->map(fn (Comment $comment) => $this->messagePayload($comment));

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
            'created_at' => $comment->CreatedOn,
        ];
    }

    private function storeComment(Ticket $ticket, string $message, ThirdPartyUser $user, User $actor): Comment
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
            ],
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ])->save();

        return $comment->refresh()->load('creator');
    }

    private function sendCreateEmails(Ticket $ticket, ThirdPartyUser $user, string $message): void
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
            (string) $this->partyId($user)
        );

        $supportSubject = "[Portal Help] New ticket {$ticketId}";
        $supportBody = "<p>New portal help ticket submitted.</p>
            <p><strong>Ticket:</strong> {$ticketId}</p>
            <p><strong>Company:</strong> " . e($user->thirdParty?->ThirdPartyName ?? 'N/A') . "</p>
            <p><strong>User:</strong> " . e($user->fullName) . " ({$user->Email})</p>
            <p><strong>Subject:</strong> " . e($ticket->Title) . "</p>
            <p><strong>Message:</strong><br>" . e($message) . '</p>';

        $this->sendEmailSafely(config('org.email'), config('org.name', 'Support Team'), $supportSubject, $supportBody);
    }

    private function sendReplyEmails(Ticket $ticket, ThirdPartyUser $user, string $message): void
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
            (string) $this->partyId($user)
        );

        $supportSubject = "[Portal Help] Reply on ticket {$ticketId}";
        $supportBody = "<p>New portal reply received.</p>
            <p><strong>Ticket:</strong> {$ticketId}</p>
            <p><strong>User:</strong> " . e($user->fullName) . " ({$user->Email})</p>
            <p><strong>Reply:</strong><br>" . e($message) . '</p>';

        $this->sendEmailSafely(config('org.email'), config('org.name', 'Support Team'), $supportSubject, $supportBody);
    }

    private function sendEmailSafely(
        ?string $email,
        string $name,
        string $subject,
        string $body,
        ?string $party = null,
        ?string $partyId = null
    ): void {
        if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            CRMEmailService::createRaw(
                SystemHelper::user(),
                $subject,
                $body,
                [[$name => $email]],
                $party,
                $partyId
            )->send(true);
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

    private function partyType(ThirdPartyUser $user): string
    {
        return $user->ThirdPartyId ? 'ThirdParty' : 'ThirdPartyUser';
    }

    private function partyId(ThirdPartyUser $user): int
    {
        return $user->ThirdPartyId ?: $user->Id;
    }
}
