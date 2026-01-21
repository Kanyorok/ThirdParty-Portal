<?php

namespace App\Traits\Controller;

use App\Enums\MeetingStatusEnum;
use App\Enums\ScheduleStatusEnum;
use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\CRM\Discussion;
use App\Models\CRM\DiscussionUser;
use App\Models\CRM\Lead;
use App\Models\CRM\Meeting;
use App\Models\CRM\MeetingRoom;
use App\Models\CRM\Notes;
use App\Models\CRM\Schedule;
use App\Models\ThirdParies\Board;
use App\Services\ActivityService;
use App\Services\MeetingService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

trait MeetingTrait
{
    /**
     * @throws Exception
     */
    public function meetings(Builder|BelongsToMany|\Illuminate\Database\Eloquent\Builder $query): JsonResponse
    {
                $tz = config('app.timezone');
                return Datatables::of($query->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
            ->editColumn('StartOn', function (Meeting $meeting) {
                                $tz = config('app.timezone');
                                return $meeting->StartOn?->format('F d, Y h:i A') . " ({$tz})";
            })->editColumn('EndOn', function (Meeting $meeting) {
                                $tz = config('app.timezone');
                                return $meeting->EndOn?->format('F d, Y h:i A') . " ({$tz})";
            })->editColumn('Title', function (Meeting $meeting) {
                                $tz = config('app.timezone');
                                return '<details><summary>' . $meeting->Title . '</summary>
                                    <p><b>Location: ' . $meeting->Location . '</b></p>
                                    <p><b>Duration: ' . $meeting->EndOn->diffForHumans($meeting->StartOn, CarbonInterface::DIFF_ABSOLUTE, parts: 2, short: true) . '</b></p>
                                    <p><small class="text-muted">Timezone: ' . e($tz) . '</small></p>
                                </details>';
            })->editColumn('StatusID', function (Meeting $meeting) {
                return $meeting->StatusID->name;
            })->setRowClass(function (Meeting $meeting) {
                if ($meeting->Type === Board::getPrimaryKey()) {
                    return 'user-select-none dbl-click-redirect-data';
                }
                return '';
            })->setRowData([
                            'dbl_click_url' => function (Meeting $meeting) {
                                if ($meeting->Type === Board::getPrimaryKey()) {
                                    return route('board-meetings.show', $meeting->MeetingID);
                                }
                                return '';
                            },
                           ])->rawColumns(['Title'])->make();
    }

    public function startClientMeeting(Client $client, string $title, string $location, Carbon $start, User $actor, Meeting $meeting = null, Schedule $schedule = null): Meeting
    {
        return DB::transaction(static function () use ($location, $title, $client, $schedule, $start, $actor, $meeting) {

            $service = ($meeting instanceof Meeting)
                ? (new MeetingService($meeting))->update(MeetingStatusEnum::Ongoing, $title, $location, $start, $actor)
                : MeetingService::create(MeetingStatusEnum::Ongoing, Client::getPrimaryKey(), $title, $location, $start, $start->copy()->addMinutes(30), $actor, '');

            $service->attachUser($actor->Id, $start, $actor);

            $service->attachClient($client->ClientID, $start, $actor);

            if ($schedule instanceof Schedule) {
                $schedule->update([
                                   'ScheduleStatusID' => ScheduleStatusEnum::Success,
                                  ]);
                //sync users and Clients
            }


            return $service->meeting;
        });
    }

    public function startLeadMeeting(Lead $lead, string $title, string|MeetingRoom $location, Carbon $start, User $actor, Meeting $meeting = null, Schedule $schedule = null): Meeting
    {
        return DB::transaction(static function () use ($location, $title, $lead, $schedule, $start, $actor, $meeting) {
            $service = ($meeting instanceof Meeting)
                ? (new MeetingService($meeting))->update(MeetingStatusEnum::Ongoing, $title, $location, $start, $actor)
                : MeetingService::create(MeetingStatusEnum::Ongoing, Lead::getPrimaryKey(), $title, $location, $start, $start->copy()->addMinutes(30), $actor, '');

            $service->attachUser($actor->Id, $start, $actor);

            $service->attachLead($lead->LeadID, $start, $actor);

            if ($schedule instanceof Schedule) {
                $schedule->update([
                                   'ScheduleStatusID' => ScheduleStatusEnum::Success,
                                  ]);

                //sync users and Leads for attendance.
            }

            return $service->meeting;
        });
    }

    public function endMeeting(Meeting $meeting, string $title, string $location, string $PartyID, Carbon $end, User $actor, string $discussion, array $UserIDs, string $notes = null): Meeting
    {
        return DB::transaction(static function () use ($UserIDs, $location, $title, $PartyID, $discussion, $notes, $end, $actor, $meeting) {

            $meeting->update([
                              'Location' => $location,
                              'Title'    => $title,
                              'EndOn'    => $end,
                              'StatusID' => MeetingStatusEnum::Completed->value,
                             ]);

            $meeting->meetingUsers()->delete();
            (new MeetingService($meeting))->attachUser($UserIDs, now(), $actor);

            $discussionID = Discussion::insertGetId([
                                                     'SourceType'   => Meeting::getPrimaryKey(),
                                                     'SourceTypeID' => $meeting->MeetingID,
                                                     "Party"        => $meeting->Type,
                                                     "PartyID"      => $PartyID,
                                                     'Discussion'   => $discussion,
                                                     'CreatedBy'    => $actor->Id,
                                                     'ModifiedBy'   => $actor->Id,
                                                     'CreatedOn'    => $end,
                                                     'ModifiedOn'   => $end,
                                                    ]);

            DiscussionUser::create([
                                    'DiscussionId' => $discussionID,
                                    'UserID'       => $actor->Id,
                                    'CreatedBy'    => $actor->Id,
                                    'ModifiedBy'   => $actor->Id,
                                    'CreatedOn'    => $end,
                                    'ModifiedOn'   => $end,
                                   ]);

            ActivityService::meeting($meeting, $PartyID, 'Meeting : ' . $meeting->Title, $actor);

            //create notes if any.
            if (is_string($notes)) {
                Notes::create([
                               'DiscussionID' => $discussionID,
                               "Party"        => $meeting->Type,
                               "PartyID"      => $PartyID,
                               'Notes'        => $notes,
                               'CreatedBy'    => $actor->Id,
                               'ModifiedBy'   => $actor->Id,
                              ]);
            }
            return $meeting;
        });
    }
}
