<?php

namespace App\Http\Controllers\CRM\Board;

use App\Enums\MeetingStatusEnum;
use App\Enums\ScheduleStatusEnum;
use App\Events\Board\BoardMeetingCanceledEvent;
use App\Events\Board\BoardMeetingUpdatedEvent;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Board\BoardMeetingRequest;
use App\Http\Requests\Board\UpdateBoardMeetingRequest;
use App\Models\CRM\Meeting;
use App\Models\CRM\MeetingRoom;
use App\Models\CRM\Schedule;
use App\Models\ThirdParies\Board;
use App\Services\MeetingService;
use App\Services\ScheduleService;
use App\Traits\Controller\MeetingTrait;
use App\Traits\Controller\ScheduleTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BoardMeetingsController extends Controller
{
    use MeetingTrait;
    use ScheduleTrait;

    public function __construct()
    {
        $this->middleware('ajax')->except('show');
    }

    /**
     * Display a listing of the resource.
     * @throws \Exception
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Board::class);

        return $this->meetings(Meeting::query()->where('t_Meetings.Type', Board::getPrimaryKey())->where('t_Meetings.EndOn', '>=', Carbon::now()));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BoardMeetingRequest $request): JsonResponse
    {
        $this->authorize('meeting', Board::class);
        $UserIds = $request->getMeetingUsers();
        $location = $request->getLocation();
        $start = $request->getStart();
        $end = $request->getEnd($start);
        $actor = $request->user();
        $committee = $request->getCommittee();

        try {
            DB::transaction(static function () use ($committee, $start, $end, $actor, $location, $UserIds, $request) {
                return ScheduleService::boardMeeting(
                    committee:$committee,
                    title: $request->validated('BoardMeetingTitle'),
                    location: $location,
                    agenda: $request->validated('BoardMeetingAgenda'),
                    start: $start,
                    end: $end,
                    actor: $actor,
                    UserIds: $UserIds
                )->schedule;
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (\Exception | \Throwable $e) {
            Log::error('Error scheduling board meeting failed:  ' . $e->getMessage());

            return $this->errored('unexpected error, try again latter');
        }

        return $this->succeeded('meeting scheduled successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $meetingId)
    {
        $this->authorize('viewAny', Board::class);
        $meeting = Meeting::query()->where('t_Meetings.Type', Board::getPrimaryKey())->where('t_Meetings.MeetingID', $meetingId)->lock('WITH(NOLOCK)')->first();
        if (! $meeting instanceof Meeting) {
            return redirect()->back()->with(['fail' => 'meeting not found']);
        }
        if ($meeting->StatusID !== MeetingStatusEnum::Scheduled) {
            return redirect()->back()->with(['fail' => 'meeting is ' . $meeting->StatusID->name]);
        }

        return view('crm.board.meetings.show')
            ->with('meeting', $meeting)
            ->with('rooms', MeetingRoom::query()->get(['RoomID', 'Name', 'Capacity']));
    }

    /**
     * Update Scheduled Meetings
     */
    public function update(UpdateBoardMeetingRequest $request, string $meetingId): JsonResponse
    {
        $this->authorize('meeting', Board::class);
        $meeting = Meeting::query()->where('t_Meetings.Type', Board::getPrimaryKey())->where('t_Meetings.MeetingID', $meetingId)->lock('WITH(NOLOCK)')->first();
        if (! $meeting instanceof Meeting) {
            return $this->errored('meeting not found');
        }

        $start = $request->getStart();
        $end = $request->getEnd($start);
        $location = $request->getLocation();

        try {
            DB::transaction(static function () use ($end, $start, $location, $meeting, $request) {
                (new MeetingService($meeting))
                    ->update(MeetingStatusEnum::Scheduled, $request->validated('BoardMeetingTitle'), $location, $start, $request->user(), $end, $request->validated('BoardMeetingAgenda'));

                $schedule = Schedule::query()->where('t_Schedule.ScheduledType', Meeting::getPrimaryKey())->where('ScheduledTypeID', $meeting->MeetingID)->first();

                if ($schedule instanceof Schedule) {
                    $schedule->update([
                                       'StartOn' => $start,
                                       'EndOn' => $end,
                                      ]);
                }
                if ($request->sendNotification()) {
                    event(new BoardMeetingUpdatedEvent($meeting));
                }

                activity()->causedBy($request->user())->performedOn($meeting)->event('updated')->log('Updated board meeting  ' . $meeting->Title . '.');
            });
        } catch (\Throwable | \Exception $e) {
            Log::error('Error updating meeting Schedule : ' . $e->getMessage());

            return $this->br_response(400, 'unexpected error, try again later');
        }

        return $this->succeeded('meeting updated successfully', route: route('board-meetings.show', $meeting->MeetingID));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $meetingId): JsonResponse
    {
        $this->authorize('meeting', Board::class);
        $meeting = Meeting::query()->where('t_Meetings.Type', Board::getPrimaryKey())->where('t_Meetings.MeetingID', $meetingId)->lock('WITH(NOLOCK)')->first();
        if (! $meeting instanceof Meeting) {
            return $this->errored('meeting not found');
        }

        try {
            DB::transaction(static function () use ($meeting, $request) {

                $meeting->update([
                                  'StatusID' => MeetingStatusEnum::Canceled,
                                 ]);
                $schedule = Schedule::query()->where('t_Schedule.ScheduledType', Meeting::getPrimaryKey())->where('ScheduledTypeID', $meeting->MeetingID)->first();

                if ($schedule instanceof Schedule) {
                    $schedule->update([
                                       'ScheduleStatusID' => ScheduleStatusEnum::Canceled,
                                      ]);
                }

                event(new BoardMeetingCanceledEvent($meeting));

                activity()->causedBy($request->user())->performedOn($meeting)->event('cancel')->log('Canceled board meeting  ' . $meeting->Title . '.');
            });
        } catch (\Throwable | \Exception $e) {
            Log::error('Error updating meeting Schedule : ' . $e->getMessage());

            return $this->br_response(400, 'unexpected error, try again later');
        }

        return $this->succeeded('meeting canceled successfully', route: route('board.index'));
    }
}
