<?php

namespace App\Services\Call;

use App\Enums\CallStatusEnum;
use App\Enums\CallTypeEnum;
use App\Enums\ScheduleStatusEnum;
use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\Communication\Call;
use App\Models\Core\Activity;
use App\Models\CRM\Contact;
use App\Models\CRM\Discussion;
use App\Models\CRM\DiscussionUser;
use App\Models\CRM\Lead;
use App\Models\CRM\Notes;
use App\Models\CRM\Schedule;
use App\Services\ActivityService;
use Carbon\Carbon;

class CallService
{
    public function __construct(public Call $call)
    {
    }

    public function setResponse(array $response): static
    {
        if (isset($response['_token'])) {
            unset($response['_token']);
        }

        $this->call->update(['Response' => $response]);

        return $this;
    }

    public function setSource(string $Source, string $SourceID): static
    {
        $this->call->update([
                             'Source' => $Source,
                             'SourceID' => $SourceID,
                            ]);

        return $this;
    }

    public static function createClient(Client $client, CallStatusEnum $callStatus, CallTypeEnum $callTypeEnum, Carbon $start, User $actor, Schedule $schedule = null): CallService
    {
        return self::_start($callStatus, $callTypeEnum, Client::getPrimaryKey(), $client->ClientID, $start, $actor, $schedule);
    }

    public static function createLead(Lead $lead, CallStatusEnum $callStatus, CallTypeEnum $callTypeEnum, Carbon $start, User $actor, Schedule $schedule = null): CallService
    {
        return self::_start($callStatus, $callTypeEnum, Lead::getPrimaryKey(), $lead->LeadID, $start, $actor, $schedule);
    }

    public static function createContact(Contact $contact, CallStatusEnum $callStatus, CallTypeEnum $callTypeEnum, Carbon $start, User $actor, Schedule $schedule = null): CallService
    {
        return self::_start($callStatus, $callTypeEnum, Contact::getPrimaryKey(), $contact->ContactID, $start, $actor, $schedule);
    }

    private static function _start(CallStatusEnum $callStatus, CallTypeEnum $callTypeEnum, string $Party, string $PartyID, Carbon $start, User $actor, Schedule $schedule = null): CallService
    {
        $call = Call::create([
                               'ScheduleID' => ($schedule instanceof Schedule) ? $schedule->ScheduleID : null,
                               "Party" => $Party,
                               "PartyID" => $PartyID,
                               'UserID' => $actor->Id,
                               'StartOn' => $start,
                               'CallStatusID' => $callStatus->value,
                               'CallTypeID' => $callTypeEnum->value,
                               'CreatedBy' => $actor->Id,
                               'ModifiedBy' => $actor->Id,
                              ]);


        if ($schedule instanceof Schedule) {
            $schedule->update([
                               'ScheduleStatusID' => ScheduleStatusEnum::Success,
                               'ScheduledTypeID' => $call->CallID,
                              ]);
        }

        return new static($call);
    }

    public function activity(User $actor, string $description = null): static
    {
        if (is_null($description)) {
            $description = ($this->call->CallTypeID->value === CallTypeEnum::Incoming->value) ? $actor->UserID . ' Received Call' : 'Called By ' . $actor->UserID;
        }
        if (
            Activity::query()->where('Party', $this->call->Party)->where('PartyID', $this->call->PartyID)
            ->where('ActivityType', Call::getPrimaryKey())->where('ActivityTypeID', $this->call->CallID)->doesntExist()
        ) {
            ActivityService::call($this->call, $description, $actor);
        }

        return $this;
    }

    public function discussion(string $discussion, User $actor, string $notes = null): static
    {
        $d = $this->call->discussion()->first();
        if (! $d instanceof Discussion) {
            $discussionID = Discussion::insertGetId([
                                                     'CreatedBy' => $actor->Id,
                                                     'SourceType' => Call::getPrimaryKey(),
                                                     'SourceTypeID' => $this->call->CallID,
                                                     "Party" => $this->call->Party,
                                                     "PartyID" => $this->call->PartyID,
                                                     'ModifiedBy' => $actor->Id,
                                                     'Discussion' => $discussion,
                                                     'CreatedOn' => now(),
                                                     'ModifiedOn' => now(),
                                                    ]);
        } else {
            $d->update([
                        'Discussion' => $d->Discussion . ' ' . $discussion,
                        'ModifiedBy' => $actor->Id,
                       ]);
            $discussionID = $d->DiscussionID;
        }

        $du = DiscussionUser::query()->where('DiscussionId', $discussionID)->where('t_DiscussionsUsers.UserID', $actor->Id)->first();
        if (! $du instanceof DiscussionUser) {
            DiscussionUser::create([
                                    'DiscussionId' => $discussionID,
                                    'UserID' => $actor->Id,
                                    'CreatedBy' => $actor->Id,
                                    'ModifiedBy' => $actor->Id,
                                    'CreatedOn' => now(),
                                    'ModifiedOn' => now(),
                                   ]);
        }

        if (is_string($notes)) {
            Notes::create([
                           'DiscussionID' => $discussionID,
                           "Party" => $this->call->Party,
                           "PartyID" => $this->call->PartyID,
                           'Notes' => $notes,
                           'CreatedBy' => $actor->Id,
                           'ModifiedBy' => $actor->Id,
                          ]);
        }

        return $this;
    }

    public function end(Carbon $end, CallStatusEnum $status, User $actor, bool $force = false): static
    {
        //check if ended
        if (is_null($this->call->EndOn)) {
            return $this->_endCall($end, $status, $actor);
        }

        if ($force) {
            return $this->_endCall($end, $status, $actor);
        }

        return $this;
    }

    protected function _endCall(Carbon $end, CallStatusEnum $status, User $actor): static
    {
        $this->call->update([
                             'EndOn' => $end,
                             'CallStatusID' => $status->value,
                             'ModifiedBy' => $actor->Id,
                            ]);

        $schedule = $this->call->schedule;
        if ($schedule instanceof Schedule) {
            $schedule->update([
                               'ScheduleStatusID' => ($status->value === CallStatusEnum::SuccessDiscussion->value) ? ScheduleStatusEnum::Success->value : ScheduleStatusEnum::PartialSuccess->value,
                               'ScheduledTypeID' => $this->call->CallID,
                              ]);
        }

        return $this;
    }
}
