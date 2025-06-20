<?php

namespace App\Services;

use App\Enums\MeetingStatusEnum;
use App\Enums\ScheduleStatusEnum;
use App\Enums\ScheduleUserStatusEnum;
use App\Events\Marketing\NewScheduleEvent;
use App\Models\Auth\User;
use App\Models\BR\Account;
use App\Models\BR\Client;
use App\Models\Communication\Call;
use App\Models\CRM\Lead;
use App\Models\CRM\Meeting;
use App\Models\CRM\MeetingRoom;
use App\Models\CRM\Schedule;
use App\Models\CRM\ScheduleClient;
use App\Models\CRM\ScheduleLead;
use App\Models\CRM\ScheduleUser;
use App\Models\CRM\Ticket;
use App\Models\HRM\Committee;
use App\Models\ThirdParies\Board;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Enums\ParticipationStatus;

class ScheduleService
{
    public function __construct(public Schedule $schedule)
    {
    }

    public function editable(): bool
    {
        return $this->schedule->ScheduleStatusID->cancelable() && Carbon::today()->startOfDay()->lte($this->schedule->EndOn);
    }

    public function cancelable(): bool
    {
        return $this->schedule->ScheduleStatusID->cancelable() &&
            ($this->schedule->CreatedOn->isToday() || Carbon::today()->endOfDay()->lte($this->schedule->EndOn));
    }

    public function actionable(): bool
    {
        return Carbon::today()->isSameDay($this->schedule->EndOn) &&
            $this->schedule->ScheduleStatusID->actionable();
    }

    public function cancelLink(string $modelID): string
    {
        if (!$this->cancelable()) {
            return '';
        }

        return match ($this->schedule->Type) {
            Client::getPrimaryKey() => route('client-schedule.destroy', [$modelID, $this->schedule->ScheduleID]),
            Lead::getPrimaryKey() => route('lead-schedule.destroy', [$modelID, $this->schedule->ScheduleID]),
            User::getPrimaryKey() => route('user-meetings.destroy', [$this->schedule->scheduled?->MeetingID]),
            default => '',
        };
    }

    public function actionLink(string $modelID): string
    {
        if (!$this->actionable()) {
            return '';
        }

        return match ($this->schedule->Type) {
            Client::getPrimaryKey() => route('clients.show', $modelID) . '/?schedule=' . $this->schedule->ScheduleID,
            Lead::getPrimaryKey() => route('leads.show', $modelID) . '/?schedule=' . $this->schedule->ScheduleID,
            default => '',
        };
    }


    public function colour(): string
    {
        return $this->schedule->ScheduleStatusID->colour((Carbon::today()->gte($this->schedule->EndOn) && ($this->schedule->ScheduledType !== Meeting::getPrimaryKey())));
    }

    public function clients($with = ['individual', 'corporate'], int $limit = 1): Builder
    {
        return Client::query()
            ->whereIn('ClientID', ScheduleClient::query()->where('ScheduleId', $this->schedule->ScheduleID)->limit($limit)->lock('WITH(NOLOCK)')->select('ClientID'))
            ->limit($limit)->lock('WITH(NOLOCK)')->with($with);
    }

    public function leads($with = ['photo'], int $limit = 1): Builder
    {
        return Lead::query()
            ->whereIn('LeadID', ScheduleLead::query()->where('ScheduleId', $this->schedule->ScheduleID)->limit($limit)->lock('WITH(NOLOCK)')->select('LeadId'))
            ->limit($limit)->lock('WITH(NOLOCK)')->with($with);
    }

    public function clientsCount(): int
    {
        return ScheduleClient::query()->where('ScheduleId', $this->schedule->ScheduleID)->count();
    }

    public function users(): Builder
    {
        return User::query()
            ->whereIn('Id', ScheduleUser::query()->where('ScheduleId', $this->schedule->ScheduleID)->select('UserID'))
            ->lock('WITH(NOLOCK)');
    }


    private static function _createNew(
        string $type,
        string $title,
        string $scheduledType,
        Carbon $start,
        Carbon $end,
        User $actor,
        string|null $scheduledTypeID = null,
        string|null $notes = '',
        string $Source = null,
        string $SourceID = null
    ): ScheduleService {
        if (!in_array($scheduledType, [Call::getPrimaryKey(), Meeting::getPrimaryKey()], true)) {
            throw new RuntimeException('invalid scheduler');
        }
        if (!in_array($type, [Lead::getPrimaryKey(), Client::getPrimaryKey(), Board::getPrimaryKey(), User::getPrimaryKey()], true)) {
            throw new RuntimeException('invalid scheduled');
        }

        $dated = now();
        $scheduleID = Schedule::insertGetId([
                                             'Title'            => $title,
                                             'Type'             => $type,
                                             'Notes'            => $notes,
                                             'ScheduledType'    => $scheduledType,
                                             'ScheduledTypeID'  => $scheduledTypeID,
                                             'ScheduleStatusID' => ScheduleStatusEnum::Scheduled->value,
                                             'StartOn'          => $start,
                                             'EndOn'            => $end,
                                             'Source'           => $Source,
                                             'SourceID'         => $SourceID,
                                             'CreatedBy'        => $actor->Id,
                                             'ModifiedBy'       => $actor->Id,
                                             'CreatedOn'        => $dated,
                                             'ModifiedOn'       => $dated,
                                            ]);


        $schedule = Schedule::query()->findOrFail($scheduleID);

        event(new NewScheduleEvent($schedule, $actor));

        return new ScheduleService($schedule);
    }

    public static function clientCall(
        Client $client,
        Carbon $start,
        Carbon $end,
        Carbon $dated,
        User $actor,
        string $notes = '',
        bool $rescheduled = false,
        array $UserIds = [],
        string $source = null,
        string $sourceID = null
    ): ScheduleService {
        $title = ($rescheduled) ? 'Rescheduled call with ' . Str::title($client->Name) . ' (' . $client->ClientID . ')' : 'Scheduled call with ' . Str::title($client->Name) . ' (' . $client->ClientID . ')';
        $service = self::_createNew(type: Client::getPrimaryKey(), title: $title, scheduledType: Call::getPrimaryKey(), start: $start, end: $end, actor: $actor, notes: $notes, Source: $source, SourceID: $sourceID)
            ->attachClient($client->ClientID, $dated, $actor);

        if (empty($UserIds)) {
            $service->attachUser($actor->Id, $dated, $actor);
        } else {
            $service->attachUser($UserIds, $dated, $actor);
        }

        return $service;
    }

    public static function leadCall(Lead $lead, Carbon $start, Carbon $end, Carbon $dated, User $actor, string $notes = '', bool $rescheduled = false, array $UserIds = []): ScheduleService
    {
        $title = ($rescheduled) ? 'Rescheduled call with ' . Str::title($lead->Name) : 'Scheduled call with ' . Str::title($lead->Name);
        $service = self::_createNew(type: Lead::getPrimaryKey(), title: $title, scheduledType: Call::getPrimaryKey(), start: $start, end: $end, actor: $actor, notes: $notes)
            ->attachLead($lead->LeadID, $dated, $actor);

        if (empty($UserIds)) {
            $service->attachUser($actor->Id, $dated, $actor);
        } else {
            $service->attachUser($UserIds, $dated, $actor);
        }

        return $service;
    }

    public static function clientMeeting(
        Client $client,
        string $title,
        string|MeetingRoom $location,
        Carbon $start,
        Carbon $end,
        Carbon $dated,
        User $actor,
        string $notes = '',
        array $UserIds = [],
        string $source = null,
        string $sourceID = null
    ): ScheduleService {
        $service = self::_meeting(type: Client::getPrimaryKey(), title: $title, location: $location, start: $start, end: $end, actor: $actor, notes: $notes, source: $source, sourceID: $sourceID)
            ->attachClient($client->ClientID, $dated, $actor);

        if (empty($UserIds)) {
            $service->attachUser($actor->Id, $dated, $actor);
        } else {
            $service->attachUser($UserIds, $dated, $actor);
        }

        return $service;
    }

    public static function leadMeeting(Lead $lead, string $title, string|MeetingRoom $location, Carbon $start, Carbon $end, Carbon $dated, User $actor, string $notes = '', array $UserIds = []): ScheduleService
    {
        $service = self::_meeting(type: Lead::getPrimaryKey(), title: $title, location: $location, start: $start, end: $end, actor: $actor, notes: $notes)
            ->attachLead($lead->LeadID, $dated, $actor);

        if (empty($UserIds)) {
            $service->attachUser($actor->Id, $dated, $actor);
        } else {
            $service->attachUser($UserIds, $dated, $actor);
        }

        return $service;
    }

    public static function boardMeeting(Committee $committee, string $title, string|MeetingRoom $location, string $agenda, Carbon $start, Carbon $end, User $actor, array $UserIds = []): ScheduleService
    {
        $dated = now();
        $service = self::_meeting(type: Board::getPrimaryKey(), title: $title, location: $location, start: $start, end: $end, actor: $actor, notes: $agenda, source: $committee->Id, sourceID: Committee::getPrimaryKey())
            ->attachBoard($committee->members()->select('t_BoardMembers.Id')->get('Id')->pluck('Id')->toArray(), $dated, $actor);

        if (empty($UserIds)) {
            $service->attachUser($actor->Id, $dated, $actor);
        } else {
            $service->attachUser($UserIds, $dated, $actor);
        }

        activity()->causedBy($actor)->performedOn($service->schedule)->event('create')->log('scheduled a board meeting for ' . $start->format('M d, Y') . '.');

        return $service;
    }

    public static function userMeeting(array $UserIds, string $title, string|MeetingRoom $location, string $agenda, Carbon $start, Carbon $end, User $actor): ScheduleService
    {
        $service = self::_meeting(type: User::getPrimaryKey(), title: $title, location: $location, start: $start, end: $end, actor: $actor, notes: $agenda)
            ->attachUser($UserIds, now(), $actor);

        activity()->causedBy($actor)->performedOn($service->schedule)->event('create')->log('scheduled staff meeting for ' . $start->format('M d, Y') . '.');

        return $service;
    }


    protected static function _meeting(
        string $type,
        string $title,
        string|MeetingRoom $location,
        Carbon $start,
        Carbon $end,
        User $actor,
        string $notes,
        string $source = null,
        string $sourceID = null
    ): ScheduleService {
        $meeting = MeetingService::create(MeetingStatusEnum::Scheduled, $type, $title, $location, $start, $end, $actor, $notes, $source, $sourceID)->meeting;

        $description = $title . " - " . (new MeetingService($meeting))->getVenue();

        return self::_createNew(type: $type, title: $title, scheduledType: Meeting::getPrimaryKey(), start: $start, end: $end, actor: $actor, scheduledTypeID: $meeting->MeetingID, notes: $description, Source: $source, SourceID: $sourceID);
    }


    /**
     * @deprecated
     */
    public static function callSchedule(): ScheduleService
    {
        return new ScheduleService(new Schedule());
    }

    /**
     * @deprecated
     */
    public static function meetingSchedule(): ScheduleService
    {
        return new ScheduleService(new Schedule());
    }

    public function attachBoard(string|array $MemberIds, Carbon $dated, User $actor): static
    {

        if (is_string($MemberIds)) {
            DB::table('t_ScheduleBoard')->insert([
                                                  'ScheduleId'     => $this->schedule->ScheduleID,
                                                  'BoardMemberId'  => $MemberIds,
                                                  'ScheduleStatus' => ScheduleUserStatusEnum::Accepted->value,
                                                  'DecidedOn'      => $dated,
                                                  'CreatedOn'      => $dated,
                                                  'CreatedBy'      => $actor->Id,
                                                  'ModifiedOn'     => $dated,
                                                  'ModifiedBy'     => $actor->Id,
                                                 ]);
            return $this;
        }

        if (is_array($MemberIds)) {
            $data = collect();
            $Members = collect($MemberIds)->unique();
            foreach ($Members->chunk(700) as $chunk) {
                foreach ($chunk as $memberID) {
                    $data->add([
                                'ScheduleId'     => $this->schedule->ScheduleID,
                                'BoardMemberId'  => $memberID,
                                'ScheduleStatus' => ScheduleUserStatusEnum::Accepted->value,
                                'DecidedOn'      => $dated,
                                'CreatedOn'      => $dated,
                                'CreatedBy'      => $actor->Id,
                                'ModifiedOn'     => $dated,
                                'ModifiedBy'     => $actor->Id,
                               ]);
                }

                if ($data->count() > 0) {
                    DB::table('t_ScheduleBoard')->insert($data->toArray());
                    $data = collect();
                }
            }
        }

        return $this;
    }

    public function attachClient(string|array $ClientIDs, Carbon $dated, User $actor, null|string $description = null): static
    {
        $description = ($description) ?? $this->schedule->Title . '  by ' . $actor->UserID;

        if (is_array($ClientIDs)) {
            $data = collect();
            $dataClients = collect();
            $clients = collect($ClientIDs)->unique();
            foreach ($clients->chunk(300) as $chunk) {//2000 attributes /6  hence 300 at a time.
                foreach ($chunk as $ClientID) {
                    $dataClients->add($ClientID);
                    $data->add([
                                'ScheduleId' => $this->schedule->ScheduleID,
                                'ClientID'   => $ClientID,
                                'CreatedOn'  => $dated,
                                'CreatedBy'  => $actor->Id,
                                'ModifiedOn' => $dated,
                                'ModifiedBy' => $actor->Id,
                               ]);
                }
                if ($data->count() > 0) {
                    DB::table('t_ScheduleClients')->insert($data->toArray());
                    ActivityService::schedule($dataClients->toArray(), Client::getPrimaryKey(), $this->schedule, $description, $actor);
                    $data = collect();
                    $dataClients = collect();
                }
            }
            return $this;
        }

        DB::table('t_ScheduleClients')->insert([
                                                'ScheduleId' => $this->schedule->ScheduleID,
                                                'ClientID'   => $ClientIDs,
                                                'CreatedOn'  => $dated,
                                                'CreatedBy'  => $actor->Id,
                                                'ModifiedOn' => $dated,
                                                'ModifiedBy' => $actor->Id,
                                               ]);

        ActivityService::schedule($ClientIDs, Client::getPrimaryKey(), $this->schedule, $description, $actor);

        return $this;
    }

    public function attachLead(string|array $LeadIds, Carbon $dated, User $actor, null|string $description = null): static
    {
        $description = ($description) ?? $this->schedule->Title . '  by ' . $actor->UserID;

        if (is_array($LeadIds)) {
            $data = collect();
            $dataLeads = collect();
            $leads = collect($LeadIds)->unique();
            foreach ($leads->chunk(300) as $chunk) {//2000 attributes /6  hence 300 at a time.
                foreach ($chunk as $LeadId) {
                    $dataLeads->add($LeadId);
                    $data->add([
                                'ScheduleId' => $this->schedule->ScheduleID,
                                'LeadId'     => $LeadId,
                                'CreatedOn'  => $dated,
                                'CreatedBy'  => $actor->Id,
                                'ModifiedOn' => $dated,
                                'ModifiedBy' => $actor->Id,
                               ]);
                }
                if ($data->count() > 0) {
                    DB::table('t_ScheduleLeads')->insert($data->toArray());
                    ActivityService::schedule($dataLeads->toArray(), Lead::getPrimaryKey(), $this->schedule, $description, $actor);
                    $data = collect();
                    $dataLeads = collect();
                }
            }
            return $this;
        }

        DB::table('t_ScheduleLeads')->insert([
                                              'ScheduleId' => $this->schedule->ScheduleID,
                                              'LeadId'     => $LeadIds,
                                              'CreatedOn'  => $dated,
                                              'CreatedBy'  => $actor->Id,
                                              'ModifiedOn' => $dated,
                                              'ModifiedBy' => $actor->Id,
                                             ]);

        ActivityService::schedule($LeadIds, Lead::getPrimaryKey(), $this->schedule, $description, $actor);

        return $this;
    }

    public function attachUser(array|string $UserIds, Carbon $dated, User $actor): static
    {
        if (is_string($UserIds)) {
            DB::table('t_ScheduleUsers')->insert([
                                                  'ScheduleId'         => $this->schedule->ScheduleID,
                                                  'UserID'             => $UserIds,
                                                  'ScheduleUserStatus' => ScheduleUserStatusEnum::Accepted->value,
                                                  'DecidedOn'          => $dated,
                                                  'CreatedOn'          => $dated,
                                                  'CreatedBy'          => $actor->Id,
                                                  'ModifiedOn'         => $dated,
                                                  'ModifiedBy'         => $actor->Id,
                                                 ]);
            return $this;
        }

        if (is_array($UserIds)) {
            $data = collect();
            $users = collect($UserIds)->unique();
            foreach ($users->chunk(1000) as $chunk) {
                foreach ($chunk as $userId) {
                    $data->add([
                                'ScheduleId'         => $this->schedule->ScheduleID,
                                'UserID'             => $userId,
                                'ScheduleUserStatus' => ScheduleUserStatusEnum::Accepted->value,
                                'DecidedOn'          => $dated,
                                'CreatedOn'          => $dated,
                                'CreatedBy'          => $actor->Id,
                                'ModifiedOn'         => $dated,
                                'ModifiedBy'         => $actor->Id,
                               ]);
                }

                if ($data->count() > 0) {
                    DB::table('t_ScheduleUsers')->insert($data->toArray());
                    $data = collect();
                }
            }
        }

        return $this;
    }


    public function getEmailContent(bool $reminder = false): string
    {
        if ($this->schedule->ScheduledType === Call::getPrimaryKey()) {
            $body = ($reminder)
                ? "<p>This is a friendly reminder that you have a scheduled call with ##NAME ##USER, on " . $this->schedule->StartOn?->format('l, jS F Y') . ".</p>"
                : "<p>Heads up; you have a scheduled call with ##NAME ##USER, on " . $this->schedule->StartOn?->format('l, jS F Y') . ".</p>";

            $body .= "<p> Details:</p>
            <ul><li><b>Date</b>&nbsp;" . $this->schedule->StartOn?->format('l, jS F Y') . "</li><li><b>Time</b>&nbsp;" . $this->schedule->StartOn?->format('h:i A') . "</li>
            <li><b>##USER</b>&nbsp;##NAME (##RELATEDID)</li><li><b>Notes</b>&nbsp;" . $this->schedule->Notes . "</li> </ul><p>More details are available on the crm dashboard</p>";
            $body = Str::replace('##type', 'call', $body);
            if ($this->schedule->Type === Client::getPrimaryKey()) {
                $client = Client::query()->where('ClientID', $this->schedule->scheduleClients()->first()?->ClientID)->first(['ClientID', 'Name']);
                if ($client instanceof Client) {
                    return Str::replace(["##NAME", "##USER", "##RELATEDID"], [$client->Name, 'member', $client->ClientID], $body);
                }

                return Str::replace(["##NAME", "##USER", "##RELATEDID"], ['unknown', 'member', ''], $body);
            }

            if ($this->schedule->Type === Lead::getPrimaryKey()) {
                $lead = $this->schedule->leads()->first(['LeadID', 'Name']);
                if ($lead instanceof Lead) {
                    return Str::replace(["##NAME", "##USER", "##RELATEDID"], [$lead->Name, 'lead', Str::padLeft($lead->LeadID, 5, '0')], $body);
                }
                return Str::replace(["##NAME", "##USER", "##RELATEDID"], ['unknown', 'lead', ''], $body);
            }

            return Str::replace(["##NAME", "##USER", "##RELATEDID"], ['unknown', ' ? ', ''], $body);
        }

        if ($this->schedule->ScheduledType === Meeting::getPrimaryKey()) {
            $body = ($reminder)
                ? "<p>This is a friendly reminder that you have a meeting appointment with ##NAME ##USER, on " . $this->schedule->StartOn?->format('l, jS F Y') . ".</p>"
                : "<p>Heads up; you have a scheduled meeting with ##NAME ##USER, on " . $this->schedule->StartOn?->format('l, jS F Y') . ".</p>";
            $body .= "<p> Details:</p><ul><li><b>Subject</b>&nbsp;" . $this->schedule->Title . "</li>";

            $meeting = $this->schedule->scheduled;
            if ($meeting instanceof Meeting) {
                $body .= "<li><b>Location</b>&nbsp;" . $meeting->Location . "</li>";
            }
            $body .= "<li><b>Date</b>&nbsp;" . $this->schedule->StartOn?->format('l, jS F Y') . "</li><li><b>Time</b>&nbsp;" . $this->schedule->StartOn?->format('h:i A') . "</li>
            <li><b>##USER</b>&nbsp;##NAME ##RELATEDID</li><li><b>Notes</b>&nbsp;" . $this->schedule->Notes . "</li> </ul><p>More details are available on the crm dashboard</p>";

            if ($this->schedule->Type === Client::getPrimaryKey()) {
                $clients_count = $this->schedule->scheduleClients()->count();
                if ($clients_count === 1) {
                    $client = Client::query()->where('ClientID', $this->schedule->scheduleClients()->first()->ClientID)->first(['ClientID', 'Name']);
                    if ($client instanceof Client) {
                        return Str::replace(["##NAME", "##USER", "##RELATEDID"], [$client->Name, '(member)', '(' . $client->ClientID . ')'], $body);
                    }
                    return Str::replace(["##NAME", "##USER", "##RELATEDID"], ['a', 'member', ''], $body);
                }
                if ($clients_count > 0) {
                    return Str::replace(["##NAME", "##USER", "##RELATEDID"], [implode(', ', $this->schedule->scheduleClients()->limit(5)->get(['ClientID'])->toArray()), '(members)', ($clients_count >= 5) ? '....(' . $clients_count . ')' : ''], $body);
                }
                return Str::replace(["##NAME", "##USER", "##RELATEDID"], ['multiple', 'members', ''], $body);
            }

            if ($this->schedule->Type === Lead::getPrimaryKey()) {
                $leads_count = $this->schedule->leads()->count();
                if ($leads_count === 1) {
                    $lead = $this->schedule->leads()->first(['LeadID', 'Name']);
                    if ($lead instanceof Lead) {
                        return Str::replace(["##NAME", "##USER", "##RELATEDID"], [$lead->Name, 'lead', Str::padLeft($lead->LeadID, 5, '0')], $body);
                    }
                    return Str::replace(["##NAME", "##USER", "##RELATEDID"], ['a', 'lead', ''], $body);
                }
                if ($leads_count > 0) {
                    $leadIds = $this->schedule->leads()->limit(5)->get(['LeadID'])->map(function (string $LeadID) {
                        return Str::padLeft($LeadID, 5, '0');
                    })->toArray();
                    return Str::replace(["##NAME", "##USER", "##RELATEDID"], [implode(', ', $leadIds), 'leads', ($leads_count >= 5) ? '....(' . $leads_count . ')' : ''], $body);
                }
                return Str::replace(["##NAME", "##USER", "##RELATEDID"], ['multiple leads', 'lead', ''], $body);
            }

            return Str::replace(["##NAME", "##USER", "##RELATEDID"], ['someone', '  ', ''], $body);
        }

        $body = ($reminder)
            ? "<p>This is a friendly reminder that you have a scheduled event, on " . $this->schedule->StartOn?->format('l, jS F Y') . ".</p>"
            : "<p>Heads up; you have a scheduled event, on " . $this->schedule->StartOn?->format('l, jS F Y') . ".</p>";

        $body .= "<p> Details:</p>
            <ul><li><b>Date</b>&nbsp;" . $this->schedule->StartOn?->format('l, jS F Y') . "</li><li><b>Time</b>&nbsp;" . $this->schedule->StartOn?->format('h:i A') . "</li>
            <li><b>Notes</b>&nbsp;" . $this->schedule->Notes . "</li> </ul><p>More details are available on the crm dashboard</p>";
        return $body;
    }

    public function getEmailICS(): string
    {
        $event = Event::create()->startsAt($this->schedule->StartOn)->endsAt($this->schedule->EndOn);
        if ($this->schedule->ScheduledType === Call::getPrimaryKey()) {
            $name = "Scheduled Call on " . $this->schedule->StartOn?->format('l, jS F Y');

            $event->address('Online')->alertMinutesBefore(20, 'You have a scheduled Call in 20 minutes.');
        } elseif ($this->schedule->ScheduledType === Meeting::getPrimaryKey()) {
            $meeting = $this->schedule->scheduled;
            if ($meeting instanceof Meeting) {
                $name = $meeting->Title;
                $event->address((new MeetingService($meeting))->getVenue(true));
            } else {
                $name = "Meeting on " . $this->schedule->StartOn?->format('l, jS F Y');
            }

            $event->description('Meeting schedule on ')->alertMinutesBefore(60, 'You have a scheduled meeting in one hour.');
        } else {
            $name = "Scheduled event on " . $this->schedule->StartOn?->format('l, jS F Y');

            $event->alertMinutesBefore(20, 'You have a scheduled event in 20 minutes.');
        }

        foreach ($this->schedule->users()->get(['Name', 'Email']) as $user) {
            $event->attendee($user->Email, $user->Email, ParticipationStatus::needs_action(), requiresResponse: true);
        }

        if ($this->schedule->Type === Client::getPrimaryKey()) {
            foreach ($this->schedule->clients()->whereNotNull('Email')->get(['ClientID', 'Name', 'Email']) as $client) {
                $event->attendee($client->Email, $client->Name . ' - ' . $client->ClientID, ParticipationStatus::accepted());
            }
        }

        if ($this->schedule->Type === Lead::getPrimaryKey()) {
            foreach ($this->schedule->leads()->whereNotNull('Email')->get(['LeadID', 'Name', 'Email']) as $lead) {
                $event->attendee($lead->Email, $lead->Name, ParticipationStatus::accepted());
            }
        }

        if ($this->schedule->Type === Board::getPrimaryKey()) {
            foreach ($this->schedule->members()->whereNotNull('t_BoardMembers.Email')->get(['t_BoardMembers.Name', 't_BoardMembers.Email']) as $board) {
                $event->attendee($board->Email, $board->Name, ParticipationStatus::accepted());
            }
        }

        $event->name($name)->createdAt($this->schedule->CreatedOn)
            ->organizer($this->schedule->creator->Email, $this->schedule->creator->Name)
            ->attendee($this->schedule->creator->Email, $this->schedule->creator->Name, ParticipationStatus::accepted());
        return Str::of(Calendar::create($name)->event($event)->get())->replace('spatie/icalendar-generator', '-//' . config('org.name') . '//Banking Realm CRM//EN')->toString();
    }

    public function getEmailSubject(bool $reminder = false): string
    {
        if ($this->schedule->ScheduledType === Call::getPrimaryKey()) {
            $subject = ($reminder)
                ? "Reminder: Scheduled Call "
                : "You have a Scheduled Call ";

            $subject .= ($this->schedule->StartOn->isToday())
                ? " in " . $this->schedule->StartOn->diffForHumans(syntax: true, parts: 2)
                : " on " . $this->schedule->StartOn?->format('l, jS F Y');

            return $subject;
        }

        if ($this->schedule->ScheduledType === Meeting::getPrimaryKey()) {
            $subject = ($reminder)
                ? "Reminder: Scheduled Meeting "
                : "You have a meeting appointment ";

            $subject .= ($this->schedule->StartOn->isToday())
                ? " in " . $this->schedule->StartOn->diffForHumans(syntax: true, parts: 2)
                : " on " . $this->schedule->StartOn?->format('l, jS F Y');

            return $subject;
        }

        $subject = ($reminder)
            ? "Reminder: Scheduled event "
            : "You have a scheduled event ";

        $subject .= ($this->schedule->StartOn->isToday())
            ? " in " . $this->schedule->StartOn->diffForHumans(syntax: true, parts: 2)
            : " on " . $this->schedule->StartOn?->format('l, jS F Y');

        return $subject;
    }

    public function source(): string
    {
        $source = $this->schedule->source;
        if ($source instanceof Account) {
            return 'Loan Ac ' . $source->AccountID;
        }

        if ($source instanceof Ticket) {
            return 'Ticket : ' . $source->TicketID;
        }

        return 'None';
    }
}
