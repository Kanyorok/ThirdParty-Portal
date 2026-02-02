<?php

namespace App\Services;

use App\Models\Auth\User;
use App\Models\BR\Account;
use App\Models\BR\Client;
use App\Models\Core\Task;
use App\Models\CRM\Campaign;
use App\Models\CRM\CampaignParty;
use App\Models\CRM\Lead;
use App\Models\CRM\Ticket;
use App\Services\HRM\UserService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use RuntimeException;

class TaskService
{
    public function __construct(public Task $task)
    {
    }

    public static function createClient(Client $client, Carbon $Due, string $Description, User $assignee, User $actor, string $Source = null, string $SourceID = null): static
    {
        return self::_create(Client::getPrimaryKey(), $client->ClientID, $Due, $Description, $actor, $Source, $SourceID)->assign($assignee);
    }

    public static function createLead(Lead $lead, Carbon $Due, string $Description, User $assignee, User $actor, string $Source = null, string $SourceID = null): static
    {
        return self::_create(Lead::getPrimaryKey(), $lead->LeadID, $Due, $Description, $actor, $Source, $SourceID)->assign($assignee);//addActivity($actor, $actor->UserID . ' added a task.');;
    }

    public function addActivity(User $actor, string $description): array
    {
        return ActivityService::task($this->task, $description, $actor);
    }

    protected static function _create(string $Party, string $PartyID, Carbon $Due, string $Description, User $actor, string $Source = null, string $SourceID = null): self
    {
        if (! in_array($Party, [Client::getPrimaryKey(), Lead::getPrimaryKey()], true)) {
            throw new RuntimeException("Invalid party in task service");
        }
        $task = new Task();
        $task->fill([
                     "Party" => $Party,
                     "PartyID" => $PartyID,
                     "UserID" => $actor->Id,
                     "Dated" => $Due,
                     "Notes" => $Description,
                     'Source' => $Source,
                     'SourceID' => $SourceID,
                     'CreatedBy' => $actor->Id,
                     'ModifiedBy' => $actor->Id,
                    ])->save();

        activity()->causedBy($actor)->performedOn($task)->event('create')->log('created a task: ' . $task->TaskID);

        return new self($task);
    }

    public function setSource(string $Source, string $SourceID): static
    {
        $this->task->update([
                             'Source' => $Source,
                             'SourceID' => $SourceID,
                            ]);

        return $this;
    }

    public function isOverdue(): bool
    {
        return $this->task->Dated->lt(now()->startOfDay());
    }

    public function updateUrl(): ?string
    {
        if ($this->task->party instanceof Lead) {
            return route('lead-tasks.update', [$this->task->party->LeadID, $this->task->TaskID]);
        }
        if ($this->task->party instanceof Client) {
            return route('client-tasks.update', [$this->task->party->ClientID, $this->task->TaskID]);
        }

        return null;
    }

    public function canClose(User $user): bool
    {
        if ($this->task->UserID === $user->Id) {
            return true;
        }

        return ($this->task->CreatedBy === $user->Id);
    }

    public function source(): string
    {
        $source = $this->task->source;
        if ($source instanceof CampaignParty) {
            if ($source->campaign instanceof Campaign) {
                return 'Campaign: <a href="' . route('campaigns.show', [$source->campaign->CampaignID]) . '">' . Str::limit($source->campaign->Label) . '<a>';
            }

            return 'Campaign: unknown';
        }

        if ($source instanceof Account) {
            return 'Debt Recovery: Ac ' . $source->AccountID;
        }

        if ($source instanceof Ticket) {
            return 'Ticket : ' . $source->TicketID;
        }

        return 'None';
    }

    public function assign(User $assignee): static
    {
        if ($assignee->Id === $this->task->UserID) {
            return $this;
        }

        $this->task->lock('WITH(NOLOCK)')->update([
                                                   "UserID" => $assignee->Id,
                                                  ]);

        (new UserService($assignee))->sendEmail(
            'Task Assignment Notification ',
            '<p>This is to inform you that a new task ([Task ID: #' . $this->task->TaskID . ']) with the following details. </p>
                    <p><b>Description</b>: ' . $this->task->Notes . '</p>
                    <p><b>Due Date </b>: ' . $this->task->Dated->format('M d, Y') . '</p>
                    <p>Please review the task and complete it by the given due date. You can access the tasks on the dashboard</p>
                    <p>Thank you for your prompt attention to this matter.</p>'
        );

        return $this;
    }
}
