<?php

namespace App\Http\Controllers\Settings\Users;

use App\Enums\MeetingStatusEnum;
use App\Enums\ScheduleStatusEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UserMeetingRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\Auth\User;
use App\Models\CRM\Meeting;
use App\Models\CRM\Schedule;
use App\Services\ScheduleService;
use App\Traits\Controller\MeetingTrait;
use App\Traits\Controller\ScheduleTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserMeetingsController extends Controller
{
    use MeetingTrait;
    use ScheduleTrait;

    public function __construct()
    {
        $this->middleware('ajax')->except('show');
        // $this->authorizeResource(Board::class);
    }

    /**
     * Display a listing of the resource.
     * @throws \Exception
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        return $this->meetings(Meeting::query()->where('t_Meetings.Type', User::getPrimaryKey())->where('t_Meetings.EndOn', '>=', Carbon::now()));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserMeetingRequest $request): JsonResponse
    {
        $this->authorize('meetings', User::class);
        $UserIds = $request->getMeetingUsers();
        $location = $request->getLocation();
        $start = $request->getStart();
        $end = $request->getEnd($start);
        $actor = $request->user();
        try {
            $schedule = DB::transaction(static function () use ($start, $end, $actor, $location, $UserIds, $request) {
                return ScheduleService::userMeeting(
                    UserIds: $UserIds,
                    title: $request->string('StaffMeetingTitle'),
                    location: $location,
                    agenda: $request->string('StaffMeetingAgenda'),
                    start: $start,
                    end: $end,
                    actor: $actor
                )->schedule;
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (\Exception | \Throwable $e) {
            Log::error('Error scheduling staff meeting failed: ');
            Log::error($e);
            return $this->errored('unexpected error, try again latter');
        }

        return $this->succeeded('meeting scheduled successfully', data: ['event' => new ScheduleResource($schedule)]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $meetingId): JsonResponse
    {
        $this->authorize('meeting', User::class);
        $meeting = Meeting::query()->where('t_Meetings.Type', User::getPrimaryKey())->where('t_Meetings.MeetingID', $meetingId)->lock('WITH(NOLOCK)')->first();
        if (!$meeting instanceof Meeting) {
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

                //event(new BoardMeetingCanceledEvent($meeting));

                activity()->causedBy($request->user())->performedOn($meeting)->event('cancel')->log('Canceled staff meeting  ' . $meeting->Title . '.');
            });
        } catch (\Throwable | \Exception $e) {
            Log::error('Error updating meeting Schedule : ' . $e->getMessage());
            return $this->br_response(400, 'unexpected error, try again later');
        }

        return $this->succeeded('meeting canceled successfully', route: route('schedule.index'));
    }
}
