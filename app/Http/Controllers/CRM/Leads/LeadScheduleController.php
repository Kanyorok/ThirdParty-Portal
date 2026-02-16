<?php

namespace App\Http\Controllers\CRM\Leads;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\CallScheduleRequest;
use App\Http\Requests\Schedule\MeetingScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\Core\Activity;
use App\Models\CRM\Lead;
use App\Models\CRM\Schedule;
use App\Services\ActivityService;
use App\Traits\Controller\ScheduleTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LeadScheduleController extends Controller
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
    public function index(Lead $lead): JsonResponse
    {
        return $this->schedules($lead->schedules());
    }

    /**
     * appointment
     *
     * @throws ValidationException
     */
    public function meeting(MeetingScheduleRequest $request, Lead $lead): JsonResponse
    {
        $start = $request->getStart();
        $end = $request->getEnd($start);
        $notes = $request->getNotes();
        $actor = $request->user();
        $location = $request->getLocation();
        $assignees = $request->getAssignees();

        try {
            $schedule = DB::transaction(function () use ($location, $assignees, $notes, $lead, $end, $actor, $start, $request) {
                return $this->appointment(
                    model: $lead,
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
        } catch (Exception | \Throwable $e) {
            Log::error('Error scheduling lead appointment failed:  ' . $e->getMessage());

            return $this->errored('unexpected error, try again latter');
        }

        $activity = $lead->activities()->where('ActivityType', Schedule::getPrimaryKey())->where('ActivityTypeID', $schedule->ScheduleID)->latest()->first();

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
    public function call(CallScheduleRequest $request, Lead $lead): JsonResponse
    {
        $start = $request->getStart();
        $end = $request->getEnd($start);
        $notes = $request->getNotes();
        $actor = $request->user();
        $assignee = $request->getAssignee();

        try {
            $schedule = DB::transaction(function () use ($assignee, $notes, $end, $actor, $lead, $start) {
                return $this->phoneCall(model: $lead, start: $start, end: $end, dated: now(), actor: $actor, notes: $notes, UserIds: [$assignee->Id]);
            });
        } catch (Exception $e) {
            Log::error('Error scheduling lead call failed:  ' . $e->getMessage());

            return $this->errored('unexpected error scheduling call, try again latter');
        }

        $activity = $lead->activities()->where('ActivityType', Schedule::getPrimaryKey())->where('ActivityTypeID', $schedule->ScheduleID)->latest()->first();

        if ($activity instanceof Activity) {
            $activity = ActivityService::rendering($activity);
        } else {
            $activity = [];
        }

        return $this->succeeded('call scheduled successfully', data: ['event' => new ScheduleResource($schedule), 'activity' => $activity]);
    }

    //todo task

    /**
     * cancel
     */
    public function destroy(Request $request, Lead $lead, $schedule_id): JsonResponse
    {
        $schedule = $lead->schedules()->where('t_Schedule.ScheduleID', $schedule_id)->first();
        if (! $schedule instanceof Schedule) {
            return $this->errored('could not find that schedule.');
        }

        try {
            DB::transaction(function () use ($lead, $request, $schedule) {
                $this->cancel($lead, $schedule, $request->user());
            });
        } catch (Exception $e) {
            Log::error('Cancel lead schedule failed:  ' . $e->getMessage());

            return $this->errored('unexpected error, try again latter');
        }

        return $this->succeeded('schedule canceled successfully', data: ['schedule_id' => $schedule->id]);
    }
}
