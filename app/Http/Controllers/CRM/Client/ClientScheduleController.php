<?php

namespace App\Http\Controllers\CRM\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\CallScheduleRequest;
use App\Http\Requests\Schedule\MeetingScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\BR\Client;
use App\Models\Core\Activity;
use App\Models\CRM\Schedule;
use App\Services\ActivityService;
use App\Traits\Controller\ScheduleTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ClientScheduleController extends Controller
{
    use ScheduleTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     * @throws Exception
     */
    public function index(Client $client): JsonResponse
    {
        return $this->schedules($client->schedules()->where('t_Schedule.StartOn', '>', now()->startOfDay()));
    }

    /**
     * appointment
     *
     * @throws ValidationException
     */
    public function meeting(MeetingScheduleRequest $request, Client $client): JsonResponse
    {
        $start = $request->getStart();
        $end = $request->getEnd($start);
        $notes = $request->getNotes();
        $actor = $request->user();
        $assignees = $request->getAssignees();
        $location = $request->getLocation();

        try {
            $schedule = DB::transaction(function () use ($location, $assignees, $notes, $client, $end, $actor, $start, $request) {
                return $this->appointment(
                    model: $client,
                    title: $request->meeting_title,
                    location: $location,
                    start: $start,
                    end: $end,
                    dated: now(),
                    actor: $actor,
                    notes: $notes,
                    UserIds: $assignees->pluck('Id')->toArray()
                );
            });
        } catch (Throwable | Exception $e) {
            Log::error('Error scheduling client appointment failed:  ' . $e->getMessage());

            return $this->errored('unexpected error, try again latter');
        }

        $activity = $client->activities()->where('ActivityType', Schedule::getPrimaryKey())->where('ActivityTypeID', $schedule->ScheduleID)->latest()->first();

        if ($activity instanceof Activity) {
            $activity = ActivityService::rendering($activity);
        } else {
            $activity = [];
        }

        return $this->succeeded('appointment scheduled successfully', data: ['event' => new ScheduleResource($schedule), 'activity' => $activity]);
    }

    /**
     * @throws ValidationException
     */
    public function call(CallScheduleRequest $request, Client $client): JsonResponse
    {
        $start = $request->getStart();
        $end = $request->getEnd($start);
        $notes = $request->getNotes();
        $actor = $request->user();
        $assignee = $request->getAssignee();

        try {
            $schedule = DB::transaction(function () use ($assignee, $notes, $end, $actor, $client, $start) {
                return $this->phoneCall(model: $client, start: $start, end: $end, dated: now(), actor: $actor, notes: $notes, UserIds: [$assignee->Id]);
            });
        } catch (Throwable | Exception $e) {
            Log::error('Error scheduling client call failed:  ' . $e->getMessage());

            return $this->errored('unexpected error scheduling call, try again latter');
        }

        $activity = $client->activities()->where('ActivityType', Schedule::getPrimaryKey())->where('ActivityTypeID', $schedule->ScheduleID)->latest()->first();

        if ($activity instanceof Activity) {
            $activity = ActivityService::rendering($activity);
        } else {
            $activity = [];
        }

        return $this->succeeded('call scheduled successfully', data: ['event' => new ScheduleResource($schedule), 'activity' => $activity]);
    }

    /**
     * cancel
     */
    public function destroy(Request $request, Client $client, $schedule_id): JsonResponse
    {
        $schedule = $client->schedules()->where('t_Schedule.ScheduleID', $schedule_id)->first();
        if (! $schedule instanceof Schedule) {
            return $this->errored('could not find that schedule.');
        }

        try {
            DB::transaction(function () use ($client, $request, $schedule) {
                $this->cancel($client, $schedule, $request->user());
            });
        } catch (Throwable | Exception $e) {
            Log::error('Cancel client schedule failed:  ' . $e->getMessage());

            return $this->errored('unexpected error, try again latter');
        }

        return $this->succeeded('schedule canceled successfully', data: ['schedule_id' => $schedule->id]);
    }
}
