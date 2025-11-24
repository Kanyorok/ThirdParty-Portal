<?php

namespace App\Services;

use App\Enums\Core\RoleEnum;
use App\Enums\TicketPriorityEnum;
use App\Enums\TicketStatusEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\Communication\Comment;
use App\Models\Communication\Email;
use App\Models\Core\CodeDetail;
use App\Models\CRM\Lead;
use App\Models\CRM\Ticket;
use App\Models\CRM\TicketUsers;
use App\Models\DMS\Image;
use App\Models\Core\Approval\CodeDetail;ce;
use App\Services\Core\ApprovalWorkflowService;
use App\Services\DMS\ImageService;
use App\Services\HRM\UserService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class TicketService extends ApprovalWorkflowService
{
    public const string ALL = 'all';

    public function __construct(public Ticket $ticket)
    {
    }

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
            /*'Status' => TicketStatusEnum::Active->value,*/
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
        $number = Ticket::query()->withTrashed()->count();
        do {
            $number++;
            $slug = Str::slug('T' . Str::padLeft(($number), 4, '0'));
        } while (Ticket::where('TicketID', $slug)->withTrashed()->exists());

        return $slug;
    }

    public static function user(CodeDetail $category, string $title, string $description, User $actor, string $Source, TicketPriorityEnum $priority, string $SourceID = '0', $start = null, $end = null): TicketService
    {
        //  $service->sendMessage('New ticket (Ticket ID: #' . $service->ticket->TicketID . ') has been created for your issue, you will receive updates', $actor, $lead);
        return self::_create($actor->Id, User::getPrimaryKey(), $category, $title, $description, $actor, $Source, $SourceID, $priority, $start, $end);
    }

    public function addWatcher(User|Team $watcher, RoleEnum $role, User $actor, bool $notify = true): static
    {
        if ($watcher instanceof Team) {
            $ticketUser = $this->ticket->watchers()->lock('WITH(NOLOCK)')
                ->where('Party', Team::getPrimaryKey())->where('PartyID', $watcher->TeamID)->first();
            if (!$ticketUser instanceof TicketUsers) {
                $ticketUser = new TicketUsers();
                $ticketUser->fill([
                    'Party' => Team::getPrimaryKey(),
                    'CreatedBy' => $actor->Id,
                    'TicketID' => $this->ticket->Id,
                    'PartyID' => $watcher->TeamID,
                    'CreatedOn' => now(),
                ]);
            }
            $ticketUser->fill([
                'Role' => $role->value,
                'ModifiedBy' => $actor->Id,
                'ModifiedOn' => now(),
            ])->save();

            if ($notify) {
                $users = $watcher->users()->lock('WITH(NOLOCK)')->select(['Email', 'Name'])->lock('WITH(NOLOCK)')->inRandomOrder()->limit(15)->get(['Email', 'Name']);
                $cc = $users->map(function ($user) {
                    return [$user->Name => $user->Email];
                });

                CRMEmailService::createTeam(
                    $watcher,
                    'Notification: Added as Watchers to Ticket ' . $this->ticket->TicketID,
                    '<p>You have been added as watchers to <a  href="' . route('tickets.show', $this->ticket->TicketID) . '">Ticket ID: #' . $this->ticket->TicketID . '</a>.</p>
                       <p>As watchers, you will receive updates and notifications about any changes, comments, or progress related to this ticket. </p>
                        <p>Please feel free to review the details and provide any necessary input to ensure a smooth resolution.</p>',
                    SystemHelper::user(),
                    $cc->toArray()
                );
            }

            return $this;
        }


        $ticketUser = $this->ticket->watchers()->lock('WITH(NOLOCK)')
            ->where('Party', User::getPrimaryKey())->where('PartyID', $watcher->Id)->first();

        if (!$ticketUser instanceof TicketUsers) {
            $ticketUser = new TicketUsers();
            $ticketUser->fill([
                'TicketID' => $this->ticket->Id,
                'Party' => User::getPrimaryKey(),
                'PartyID' => $watcher->Id,
                'CreatedBy' => $actor->Id,
                'CreatedOn' => now(),
            ]);
        }
        $ticketUser->fill([
            'Role' => $role->value,
            'ModifiedBy' => $actor->Id,
            'ModifiedOn' => now(),
        ])->save();


        if ($notify) {
            CRMEmailService::createUser(
                user: $watcher,
                subject: 'Notification: Added as a Watcher to Ticket ' . $this->ticket->TicketID,
                body: '<p>You have been added as watcher to <a  href="' . route('tickets.show', $this->ticket->TicketID) . '">Ticket ID: #' . $this->ticket->TicketID . '</a>.</p>
                       <p>As watchers, you will receive updates and notifications about any changes, comments, or progress related to this ticket. </p>
                        <p>Please feel free to review the details and provide any necessary input to ensure a smooth resolution.</p>',
                actor: SystemHelper::user()
            );
        }
        return $this;
    }

    public function sendMessage(string $message, User $actor, $model = null, int $loop = 0): void
    {
        if ($loop > 2) {//break;
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
                    return $ticket->category->Description;
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
                //  return '<a href="' . route('tickets.show', [$ticket->TicketID]) . '"">' . $ticket->TicketID . '</a>';
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
    public function assign(User|Team $owner): static
    {
        //check a previous owner as read.
        $this->ticket->watchers()->lock('WITH(NOLOCK)')->where('t_TicketUsers.Party', $this->ticket->Owner)
            ->where('t_TicketUsers.PartyID', $this->ticket->OwnerID)->update([
                'Role' => RoleEnum::Read->value,
            ]);

        $this->addWatcher($owner, RoleEnum::Admin, SystemHelper::user(), false);

        if ($owner instanceof Team) {
            $this->ticket->lock('WITH(NOLOCK)')->update([
                'Owner' => Team::getPrimaryKey(),
                'OwnerID' => $owner->TeamID,
            ]);

            $users = $owner->users()->lock('WITH(NOLOCK)')->select(['Email', 'Name'])->lock('WITH(NOLOCK)')->inRandomOrder()->limit(15)->get(['Email', 'Name']);
            $cc = $users->map(function ($user) {
                return [$user->Name => $user->Email];
            });

            CRMEmailService::createTeam(
                $owner,
                'Ticket Assignment Notification ' . $this->ticket->TicketID,
                '<p>This is to inform that a new ticket (<a  href="' . route('tickets.show', $this->ticket->TicketID) . '">[Ticket ID: #' . $this->ticket->TicketID . ']</a>) has been assigned to team ' . $owner->Name . '. </p>
                       <p>You can access the ticket using the following link: <a  href="' . route('tickets.show', $this->ticket->TicketID) . '"> ticket details</a></p>
                        <p>Thank you for your prompt attention to this matter.</p>',
                SystemHelper::user(),
                $cc->toArray()
            );

            return $this;
        }

        if ($this->ticket->Party === User::getPrimaryKey() && $this->ticket->PartyID === $owner->Id) {
            throw new ErroredException('Cannot assign ticket to ' . $owner->Name);
        }

        $this->ticket->lock('WITH(NOLOCK)')->update([
            'Owner' => User::getPrimaryKey(),
            'OwnerID' => $owner->Id,
        ]);

        $service = new UserService($owner);
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
    public function deleteWatcher(TicketUsers $ticketUser, User $actor): static
    {
        if ($this->ticket->Id !== $ticketUser->TicketID) {
            throw new ErroredException('This ticket does not belong to you.');
        }

        if (($ticketUser->PartyID === $this->ticket->CreatedBy) && ($ticketUser->Party === User::getPrimaryKey())) {//check creator
            throw new ErroredException('cannot remove creator.');
        }

        if (($ticketUser->PartyID === $this->ticket->OwnerID) && ($ticketUser->Party === $this->ticket->Owner)) {//check assigned
            throw new ErroredException('cannot remove assigned.');
        }

        $service = new PartyService($ticketUser->party);
        activity()->causedBy($actor)->performedOn($this->ticket)->event('delete')->log('Removed ' . $service->getName() . ' as a ticket (' . $this->ticket->TicketID . ') watcher.');

        $ticketUser->forceFill([
            'DeletedOn' => now(),
            'DeletedBy' => $actor->Id,
        ])->save();

        $service->sendEmail(
            'Notification: Removed as Watchers from Ticket ' . $this->ticket->TicketID,
            '<p>You have been removed as watchers from Ticket ' . $this->ticket->TicketID . '. As a result, you will no longer receive updates or notifications related to this ticket.</p>
                <p>Thank you for your continued support and collaboration.</p>'
        );
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

    public function cancel(User $actor): static
    {
        $this->ticket->update([
            'StatusId' => self::codeDetail(TicketStatusEnum::Cancelled, 'TicketStatus')->ID,
            // 'Status' => TicketStatusEnum::Cancelled->value,
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

    public function resolve(User $actor): static
    {
        $this->ticket->update([
            //'Status' => TicketStatusEnum::Resolved->value,
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
        $status = self::codeDetail(TicketStatusEnum::Approval, 'TicketStatus');
        if ($this->submittedAction($actor, $status, Ticket::getPrimaryKey(), $this->ticket->Id, $reason)) {
            $this->ticket->update([
                //'Status' => TicketStatusEnum::Approval->value,
                'StatusId' => $status->ID,
                'ClosedOn' => null,
                'ModifiedBy' => $actor->Id,
            ]);
            activity()->causedBy($actor)->performedOn($this->ticket)->event('reopen')->log('Reopen ticket ' . $this->ticket->TicketID . ' submitted for approval.');
            return $this;
        }
        throw new  ErroredException('unexpected error occurred.');
    }

    public function canApprove(User $actor): bool
    {
        return in_array($actor->Id, $this->ticket->pendingWorkflows()->get('t_PendingWorkflows.UserId')->pluck('UserId')->toArray(), true);
    }

    public function comment(string $description, User $actor): Comment
    {
        activity()->causedBy($actor)->performedOn($this->ticket)->event('comment')->log('commented on ' . $this->ticket->TicketID);
        return CommentService::forTicket($this->ticket, $description, $actor)->comment;
    }

    /**
     * @throws ErroredException
     */
    public function workflowApprove(User $actor): static
    {
        if ($this->approveAction($actor, TicketStatusEnum::Active->codeDetail(), Ticket::getPrimaryKey(), $this->ticket->Id, 'approved', 'StatusId')) {
            $this->ticket->forceFill([
                //'Status' => TicketStatusEnum::Active,
                'ClosedOn' => null,
            ])->save(['timestamps' => false]);
            activity()->causedBy($actor)->performedOn($this->ticket)->event('approve')->log('Approved ticket re-open ' . $this->ticket->TicketID);

            $owner = $this->ticket->modified;
            if ($owner instanceof User) {
                (new UserService($owner))->sendEmail(
                    'Ticket Update - Approval for Reopening',
                    '<p>Hello</p><p>This to inform you that your request to reopen ticket #<a href="' . route('tickets.show', [$this->ticket->TicketID]) . '">' . $this->ticket->TicketID . '</a> has been <b>approved</b>. Click the link below to review</p>
                 <p><a href="' . route('tickets.show', [$this->ticket->TicketID]) . '"> ticket details</a></p>
                 <p>Thank you for your patience.</p>'
                );
            }
            return $this;
        }
        throw new  ErroredException('unexpected error occured.');
    }

    /**
     * @throws ErroredException
     */
    public function workflowReject(User $actor, string $reason): static
    {
        $status = self::codeDetail(TicketStatusEnum::Cancelled, 'TicketStatus');
        if ($this->rejectAction($actor, $status, Ticket::getPrimaryKey(), $this->ticket->Id, $reason, 'StatusId')) {
            $this->ticket->forceFill([
                //'Status' => TicketStatusEnum::Active,
                'StatusId' => $status->ID,
                'ClosedOn' => now(),
            ])->save(['timestamps' => false]);

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
            activity()->causedBy($actor)->performedOn($this->ticket)->event('approve')->log('Approved ticket re-open ' . $this->ticket->TicketID);
            return $this;
        }
        throw new  ErroredException('unexpected error occured.');
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
}
