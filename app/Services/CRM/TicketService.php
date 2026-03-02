<?php

namespace App\Services\CRM;

use App\Enums\Core\RoleEnum;
use App\Enums\TicketPriorityEnum;
use App\Enums\TicketSourceEnum;
use App\Enums\TicketStatusEnum;
use App\Enums\WorkflowStatus;
use App\Events\Ticket\ReopenTicketEvent;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\Communication\Comment;
use App\Models\Communication\Email;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\SpecialPermission;
use App\Models\CRM\Approval\Workflow;
use App\Models\CRM\Lead;
use App\Models\CRM\Ticket;
use App\Models\DMS\Image;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyUser as PortalThirdPartyUser;
use App\Services\BR\ClientService;
use App\Services\CommentService;
use App\Services\Core\ApprovalWorkflowService;
use App\Services\CRMEmailService;
use App\Services\DMS\ImageService;
use App\Services\HRM\UserService;
use App\Services\LeadService;
use App\Services\PartyService;
use App\Traits\Services\SpecialPermissionsTrait;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;
use Yajra\DataTables\DataTables;

class TicketService extends ApprovalWorkflowService
{
    use SpecialPermissionsTrait;

    public const ALL = 'all';

    public function __construct(public Ticket $ticket)
    {
    }

    /**
     * @throws ErroredException
     */
    public static function client(Client $client, CodeDetail $category, string $title, string $description, User $actor, string $Source, TicketPriorityEnum $priority, string $SourceID = '0', $start = null, $end = null, $SourceTicketID = null): TicketService
    {
        $service = self::_create($client->ClientID, Client::getPrimaryKey(), $category, $title, $description, $actor, $Source, $SourceID, $priority, $start, $end, $SourceTicketID);
        $service->sendMessage('New ticket (Ticket ID: #' . $service->ticket->TicketID . ') has been created for your issue, you will receive updates', $actor, $client);

        return $service;
    }

    /**
     * @throws ErroredException
     */
    private static function _create(string $PartyID, string $Party, CodeDetail $category, string $title, string $description, User $actor, string $Source, string $SourceID, TicketPriorityEnum $priority, $start = null, $end = null, $SourceTicketID = null): TicketService
    {
        $ticket = new Ticket();
        $ticket->fill([
            'SourceTicketID' => $SourceTicketID,
            'TicketID' => Str::upper(self::_ID()),
            'Title' => $title,
            'CategoryID' => $category->ID,
            'Notes' => str_replace("\r\n", '', $description),
            'Party' => $Party,
            'PartyID' => $PartyID,
            'Source' => $Source,
            'SourceID' => $SourceID,

            'StatusId' => self::codeDetail(TicketStatusEnum::Active, 'TicketStatus')->ID,
            'Priority' => $priority->value,
            'StartDate' => $start,
            'EndDate' => $end,
            'Owner' => User::getPrimaryKey(),
            'OwnerID' => SystemHelper::user()->Id,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ])->save();

        activity()->causedBy($actor)->performedOn($ticket->refresh())->event('create')->log('Created ticket ' . $ticket->TicketID);

        return (new self($ticket))->addWatcher($actor, RoleEnum::Write, SystemHelper::user(), false);
    }

    protected static function _ID(): string
    {
        $number = Ticket::withTrashed()->count();
        do {
            $number++;
            $slug = Str::slug('T' . Str::padLeft(($number), 4, '0'));
        } while (Ticket::where('TicketID', $slug)->withTrashed()->exists());

        return $slug;
    }

    /**
     * @throws ErroredException
     */
    public static function user(CodeDetail $category, string $title, string $description, User $actor, string $Source, TicketPriorityEnum $priority, string $SourceID = '0', $start = null, $end = null): TicketService
    {

        return self::_create($actor->Id, User::getPrimaryKey(), $category, $title, $description, $actor, $Source, $SourceID, $priority, $start, $end);
    }

    /**
     * @throws ErroredException
     */
    public function addWatcher(User|Team $assignee, RoleEnum $role, User $actor, bool $notify = true): static
    {
        $this->_addPermissions($this->ticket, $assignee, $role, $actor, $notify);

        return $this;
    }

    public function sendMessage(string $message, User $actor, $model = null, int $loop = 0): void
    {
        if ($loop > 2) { //break;
            return;
        }

        if (is_null($model)) {
            $loop++;
            $this->sendMessage($message, $actor, $this->ticket->party, $loop);

            return;
        }

        if ($model instanceof Client) {
            (new ClientService($model))->sendMessage($message, $actor);

            return;
        }
        if ($model instanceof User) {
            (new UserService($model))->sendMessage($message, $actor);

            return;
        }
        if ($model instanceof Lead) {
            (new LeadService($model))->sendMessage($message, $actor);
        }
    }

    /**
     * @throws ErroredException
     */
    public static function codeDetailEnum(CodeDetail $status): TicketStatusEnum
    {
        return TicketStatusEnum::fromValue($status->Value);
    }

    /**
     * @throws ErroredException
     */
    public static function lead(Lead $lead, CodeDetail $category, string $title, string $description, User $actor, string $Source, TicketPriorityEnum $priority, string $SourceID = '0', $start = null, $end = null): TicketService
    {
        $service = self::_create($lead->LeadID, Lead::getPrimaryKey(), $category, $title, $description, $actor, $Source, $SourceID, $priority, $start, $end);
        $service->sendMessage('New ticket (Ticket ID: #' . $service->ticket->TicketID . ') has been created for your issue, you will receive updates', $actor, $lead);

        return $service;
    }

    /**
     * @throws Exception
     */
    public static function dt(Builder|MorphMany $query, array $with = []): JsonResponse
    {
        return Datatables::of($query->lock('WITH(NOLOCK)')->with(empty($with) ? ['status'] : array_merge($with, ['status']))->select('*'))->addIndexColumn()
            ->editColumn('category', function (Ticket $ticket) use ($with) {
                if (in_array('category', $with, true)) {
                    return $ticket->category?->Description;
                }

                return '';
            })->editColumn('party', function (Ticket $ticket) use ($with) {
                if (in_array('party', $with, true)) {
                    return (new PartyService($ticket->party))->getDTRow();
                }

                return '';
            })->addColumn('Status', function (Ticket $ticket) {
                return $ticket->status->Description;
            })->editColumn('Priority', function (Ticket $ticket) {
                return $ticket->Priority->name;
            })->addColumn('TicketID', function (Ticket $ticket) {
                return '<a href="javascript:void(0)" data-click_url="' . route('tickets.edit', [$ticket->TicketID]) . '" data-summary_title="Ticket ' . $ticket->TicketID . ' summary" class="click-summary-data">' . $ticket->TicketID . '</a>';
            })->editColumn('CreatedOn', function (Ticket $ticket) {
                return $ticket->CreatedOn?->format('F d, Y h:i A');
            })->setRowClass(function (Ticket $ticket) {
                $classes = 'mouse_pointer user-select-none dbl-click-redirect-data';
                switch ($ticket->status->Value) {
                    case TicketStatusEnum::Cancelled->value:
                        $classes .= ' text-decoration-line-through';

                        break;
                    case TicketStatusEnum::Resolved->value:
                        $classes .= ' text-muted';

                        break;
                }

                return $classes;
            })->setRowData([
                'dbl_click_url' => function (Ticket $ticket) {
                    return route('tickets.show', $ticket->TicketID);
                },
            ])->rawColumns(['TicketID', 'party'])->make();
    }

    /**
     * @throws ErroredException
     */
    public function assign(User|Team $assignee): static
    {
        //check a previous owner as read.
        $this->addWatcher($this->ticket->assignee, RoleEnum::Read, SystemHelper::user(), false);

        $this->addWatcher($assignee, RoleEnum::Admin, SystemHelper::user(), false);

        if ($assignee instanceof Team) {
            $this->ticket->lock('WITH(NOLOCK)')->update([
                'Owner' => Team::getPrimaryKey(),
                'OwnerID' => $assignee->TeamID,
            ]);

            $users = $assignee->users()->lock('WITH(NOLOCK)')->select(['Email', 'Name'])->lock('WITH(NOLOCK)')->inRandomOrder()->limit(15)->get(['Email', 'Name']);
            $cc = $users->map(function ($user) {
                return [$user->Name => $user->Email];
            });

            CRMEmailService::createTeam(
                $assignee,
                'Ticket Assignment Notification ' . $this->ticket->TicketID,
                '<p>This is to inform that a new ticket (<a  href="' . route('tickets.show', $this->ticket->TicketID) . '">[Ticket ID: #' . $this->ticket->TicketID . ']</a>) has been assigned to team ' . $assignee->Name . '. </p>
                       <p>You can access the ticket using the following link: <a  href="' . route('tickets.show', $this->ticket->TicketID) . '"> ticket details</a></p>
                        <p>Thank you for your prompt attention to this matter.</p>',
                SystemHelper::user(),
                $cc->toArray()
            );

            return $this;
        }

        if ($this->ticket->PartyID === $assignee->Id && $this->ticket->Party === User::getPrimaryKey()) {
            throw new ErroredException('Cannot assign ticket to ' . $assignee->Name);
        }

        $this->ticket->lock('WITH(NOLOCK)')->update([
            'Owner' => User::getPrimaryKey(),
            'OwnerID' => $assignee->Id,
        ]);

        $service = new UserService($assignee);
        if ($this->ticket->Priority->value === TicketPriorityEnum::Urgent->value) {
            $service->sendMessage('You have been assigned ticket (Ticket ID: #' . $this->ticket->TicketID . ') that has high priority kindly check on it.', SystemHelper::user());
        }
        $service->sendEmail(
            'Ticket Assignment Notification ' . $this->ticket->TicketID,
            '<p>This is to inform you that a new ticket ([Ticket ID: #' . $this->ticket->TicketID . ']) has been assigned to you. </p>
                    <p>Kindly review the ticket at your earliest convenience. You can access the ticket using the following link: <a  href="' . route('tickets.show', $this->ticket->TicketID) . '"> ticket details</a></p>
                    <p>Thank you for your prompt attention to this matter.</p>'
        );

        return $this;
    }

    public function update(CodeDetail $category, string $title, string $description, User $actor, $start = null, $end = null): TicketService
    {
        $this->ticket->update([
            'Title' => $title,
            'CategoryID' => $category->ID,
            'Notes' => str_replace("\r\n", '', $description),
            'StartDate' => $start,
            'EndDate' => $end,
            'ModifiedBy' => $actor->Id,
        ]);
        activity()->causedBy($actor)->performedOn($this->ticket)->event('update')->log('Updated ticket ' . $this->ticket->TicketID);

        return $this;
    }

    /**
     * @throws ErroredException
     */
    public function deleteWatcher(SpecialPermission $permission, User $actor): static
    {
        $this->ticket = $this->_trashPermissions($this->ticket, $permission, $actor);

        return $this;
    }

    public function setSource(string $Source, string $SourceID): static
    {
        $this->ticket->update([
            'Source' => $Source,
            'SourceID' => $SourceID,
        ]);

        return $this;
    }

    public function source(): string
    {
        if ($this->ticket->SourceID === '0') {
            return $this->ticket->Source;
        }

        $source = $this->ticket->source;
        if ($source instanceof Email) {
            return 'Email : <a href="javascript:void(0)" data-click_url="' . route('emails.summary', [$source->EmailID]) . '" data-summary_title="email details" class="click-summary-data">' . $source->EmailID . '</a>';
        }


        return $this->ticket->Source;
    }

    /**
     * @throws ErroredException
     */
    public function cancel(User $actor): static
    {
        $this->ticket->update([
            'StatusId' => self::codeDetail(TicketStatusEnum::Cancelled, 'TicketStatus')->ID,

            'ClosedOn' => now(),
            'ModifiedBy' => $actor->Id,
        ]);

        activity()->causedBy($actor)->performedOn($this->ticket)->event('cancel')->log('Canceled ticket ' . $this->ticket->TicketID);

        return $this;
    }

    public function priority(TicketPriorityEnum $priority): static
    {
        $this->ticket->update([
            'Priority' => $priority->value,
        ]);

        return $this;
    }

    /**
     * @throws ErroredException
     */
    public function resolve(User $actor): static
    {
        $this->ticket->update([

            'StatusId' => self::codeDetail(TicketStatusEnum::Resolved, 'TicketStatus')->ID,
            'ClosedOn' => now(),
            'ModifiedBy' => $actor->Id,
        ]);

        $this->sendMessage('Hello #name, Ticket ID: #' . $this->ticket->TicketID . ' has been resolved.', $actor);
        activity()->causedBy($actor)->performedOn($this->ticket)->event('closed')->log('Marked ticket ' . $this->ticket->TicketID . ' as resolved');

        return $this;
    }

    /**
     * @throws ErroredException
     */
    public function reopen(User $actor, string $reason): static
    {
        $currentStatus = TicketStatusEnum::fromCodeDetail($this->ticket->status);
        $status = self::codeDetail(TicketStatusEnum::Approval, 'TicketStatus');

        $this->ticket->update([

            'StatusId' => $status->ID,
            'ClosedOn' => null,
            'ModifiedBy' => $actor->Id,
        ]);

        //add workflow
        Workflow::create([
            'Source' => Ticket::getPrimaryKey(),
            'SourceID' => $this->ticket->Id,
            'Stage' => $currentStatus->value,
            'Status' => WorkflowStatus::Submitted->value,
            'Notes' => $reason,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        activity()->causedBy($actor)->performedOn($this->ticket)->event('reopen')->log('Reopen ticket ' . $this->ticket->TicketID . ' submitted for approval.');

        event(new ReopenTicketEvent($this->ticket, $actor, $reason));

        return $this;
    }

    public function canApproveTicket(User $actor): bool
    {


        return in_array($actor->Id, $this->ticket->pendingWorkflows()->get('t_PendingWorkflows_static.UserId')->pluck('UserId')->toArray(), true);
    }

    public function comment(string $description, User $actor): Comment
    {
        activity()->causedBy($actor)->performedOn($this->ticket)->event('comment')->log('commented on ' . $this->ticket->TicketID);

        $comment = CommentService::forTicket($this->ticket, $description, $actor)->comment;
        $this->notifyPortalPartyOnComment($description);

        return $comment;
    }

    /**
     * @throws ErroredException
     */
    public function workflowApprove(User $actor): static
    {
        $status = self::codeDetail(TicketStatusEnum::Active, 'TicketStatus');

        $this->ticket->forceFill([
            'StatusId' => $status->ID,

            'ClosedOn' => null,
        ])->save(['timestamps' => false]);

        $this->ticket->pendingWorkflows()->where('Stage', TicketStatusEnum::Approval)->update([
            'DeletedOn' => now(),
            'DeletedBy' => $actor->Id,
        ]);

        $this->ticket->workflows()->create([
            'Stage' => TicketStatusEnum::Approval->name,
            'Status' => WorkflowStatus::Accepted->value,
            'Notes' => 'Ticket Re-Open Approved',
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        $owner = $this->ticket->modified;
        if ($owner instanceof User) {
            (new UserService($owner))->sendEmail(
                'Ticket Update - Approval for Reopening',
                '<p>Hello</p><p>This to inform you that your request to reopen ticket #<a href="' . route('tickets.show', [$this->ticket->TicketID]) . '">' . $this->ticket->TicketID . '</a> has been <b>approved</b>. Click the link below to review</p>
                <p><a href="' . route('tickets.show', [$this->ticket->TicketID]) . '"> ticket details</a></p>
                <p>Thank you for your patience.</p>'
            );
        }

        $this->sendMessage('Hello #name, Ticket ID: #' . $this->ticket->TicketID . ' has been reopened.', $actor);

        activity()->causedBy($actor)->performedOn($this->ticket)->event('approve')->log('Approved ticket re-open ' . $this->ticket->TicketID);

        return $this;
    }

    /**
     * @throws ErroredException
     */
    public function workflowReject(User $actor, string $reason): static
    {
        $status = self::codeDetail(TicketStatusEnum::Cancelled, 'TicketStatus');
        $this->ticket->forceFill([
            'StatusId' => $status->ID,

        ])->save(['timestamps' => false]);


        $this->ticket->pendingWorkflows()->where('Stage', TicketStatusEnum::Approval)->update([
            'DeletedOn' => now(),
            'DeletedBy' => $actor->Id,
        ]);

        $this->ticket->workflows()->create([
            'Stage' => TicketStatusEnum::Approval->name,
            'Status' => WorkflowStatus::RejectedCancel->value,
            'Notes' => $reason,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);


        $owner = $this->ticket->modified;
        if ($owner instanceof User) {
            (new UserService($owner))->sendEmail(
                'Ticket Update - Request for Reopening Denied',
                '<p>Hello</p><p>This is to inform you that your request to reopen ticket #<a href="' . route('tickets.show', [$this->ticket->TicketID]) . '">' . $this->ticket->TicketID . '</a> has been <b style="color: #fa2f43">denied</b>.</p>
                <p><a href="' . route('tickets.show', [$this->ticket->TicketID]) . '"> ticket details</a></p>
                <p><b>Reason Given: </b>&nbsp;' . $reason . '</p>'
            );
        }

        activity()->causedBy($actor)->performedOn($this->ticket)->event('reject')->log('Approved ticket re-open ' . $this->ticket->TicketID);

        return $this;
    }

    public function checkOwnership(User $user): bool
    {
        if ($this->ticket->Owner === User::getPrimaryKey()) {
            return ($this->ticket->OwnerID === $user->Id);
        }

        return $user->teamUser()->where('t_TeamUser.TeamId', $this->ticket->OwnerID)->exists();
    }

    public function document(UploadedFile $file, User $actor): Image
    {
        $document = ImageService::createUpload($file, Ticket::getPrimaryKey(), $this->ticket->Id, $actor)->image;
        activity()->causedBy($actor)->performedOn($this->ticket)->event('document')->log('added a document  ' . $document->Name . ' to ticket ' . Str::upper($this->ticket->TicketID));

        return $document;
    }

    public function documentContent(string $content, string $MimeType, string $Name, User $actor): Image
    {
        $document = ImageService::create(Ticket::getPrimaryKey(), $this->ticket->Id, $content, $MimeType, $Name, $actor)->image;
        activity()->causedBy($actor)->performedOn($this->ticket)->event('document')->log('added a document  ' . $document->Name . ' to ticket ' . Str::upper($this->ticket->TicketID));

        return $document;
    }

    private function notifyPortalPartyOnComment(string $description): void
    {
        if ($this->ticket->Source !== TicketSourceEnum::Website->value) {
            return;
        }

        $recipient = $this->resolvePortalRecipient();
        $email = $recipient['email'] ?? null;

        if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $name = $recipient['name'] ?? 'Customer';
        $subject = "Support update on ticket {$this->ticket->TicketID}";
        $body = "<p>Hello " . e($name) . ",</p>
            <p>Your support ticket <strong>{$this->ticket->TicketID}</strong> has a new response.</p>
            <p><strong>Subject:</strong> " . e($this->ticket->Title) . "</p>
            <p><strong>Response:</strong><br>" . e($description) . '</p>
            <p>Please log in to the portal for more details.</p>';
        $replyToEmail = config('support.queue_email', config('org.email'));
        $replyToName = config('support.queue_name', config('org.name'));

        try {
            $service = CRMEmailService::createRaw(
                SystemHelper::user(),
                $subject,
                $body,
                [[$name => $email]],
                $recipient['party'] ?? null,
                $recipient['partyId'] ?? null
            );

            $service->crmEmail->forceFill([
                'Extra' => (object) [
                    'event' => 'ticket_reply_received',
                    'source' => 'portal_help_ticket',
                    'ticket_id' => $this->ticket->TicketID,
                    'title' => "Ticket {$this->ticket->TicketID} updated",
                    'link' => $this->portalTicketLink(),
                ],
            ])->save();

            $service->setReplyTo($replyToEmail, $replyToName)->send(true);
        } catch (Throwable $e) {
            Log::warning('Failed to send ticket response notification email.', [
                'ticket_id' => $this->ticket->TicketID,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function resolvePortalRecipient(): array
    {
        if ($this->ticket->Party === 'ThirdPartyUser') {
            $user = PortalThirdPartyUser::query()->where('Id', $this->ticket->PartyID)->first();

            return [
                'name' => $user?->fullName ?: ($user?->Email ?? 'Customer'),
                'email' => $user?->Email,
                'party' => 'ThirdPartyUser',
                'partyId' => $user ? (string) $user->Id : (string) $this->ticket->PartyID,
            ];
        }

        if ($this->ticket->Party === 'ThirdParty') {
            $party = ThirdParties::query()->where('Id', $this->ticket->PartyID)->first();
            $portalMeta = $this->latestPortalCommentMeta();

            $portalEmail = $portalMeta['author_email'] ?? null;
            $portalName = $portalMeta['author_name'] ?? null;

            if (is_string($portalEmail) && filter_var($portalEmail, FILTER_VALIDATE_EMAIL)) {
                return [
                    'name' => $portalName ?: ($party?->ThirdPartyName ?? 'Customer'),
                    'email' => $portalEmail,
                    'party' => 'ThirdParty',
                    'partyId' => (string) $this->ticket->PartyID,
                ];
            }

            $user = PortalThirdPartyUser::query()
                ->where('ThirdPartyId', $this->ticket->PartyID)
                ->whereNotNull('Email')
                ->orderByDesc('IsActive')
                ->orderByDesc('Id')
                ->first();

            return [
                'name' => $user?->fullName ?: ($party?->ThirdPartyName ?? 'Customer'),
                'email' => $user?->Email ?: $party?->Email,
                'party' => 'ThirdParty',
                'partyId' => (string) $this->ticket->PartyID,
            ];
        }

        return [
            'name' => 'Customer',
            'email' => null,
            'party' => $this->ticket->Party,
            'partyId' => (string) $this->ticket->PartyID,
        ];
    }

    private function latestPortalCommentMeta(): array
    {
        $comments = $this->ticket->comments()->latest('Id')->limit(20)->get(['Response']);

        foreach ($comments as $comment) {
            $response = $comment->Response;
            $meta = is_object($response) ? (array) $response : (is_array($response) ? $response : []);
            if (! empty($meta['author_email'])) {
                return $meta;
            }
        }

        return [];
    }

    private function portalTicketLink(): string
    {
        $baseUrl = rtrim((string) (config('app.frontend_url') ?: config('app.url')), '/');

        return "{$baseUrl}/dashboard/help/tickets/{$this->ticket->TicketID}";
    }
}
