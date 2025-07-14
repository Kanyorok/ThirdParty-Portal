<?php

namespace App\Http\Controllers\CRM\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleRequest;
use App\Http\Resources\ScheduleCollection;
use App\Http\Resources\ScheduleResource;
use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\Communication\Call;
use App\Models\CRM\Lead;
use App\Models\CRM\Meeting;
use App\Models\CRM\MeetingRoom;
use App\Models\CRM\Schedule;
use App\Models\CRM\ScheduleUser;
use App\Models\ThirdParies\Board;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Get the middleware that should be assigned to the controller.
     *
     * public static function middleware(): array
     * {
     * return [
     * new Middleware(AjaxCheckMiddleware::class, except: ['index']),
     * ];
     * } */

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|ScheduleCollection
    {
        if ($request->ajax()) {
            $start = Carbon::createFromFormat('Y-m-d', $request->start)?->startOfDay();
            $end = Carbon::createFromFormat('Y-m-d', $request->end)?->endOfDay();
            return new ScheduleCollection(
                Schedule::query()->whereIn('t_Schedule.ScheduleID', ScheduleUser::query()->where('UserID', $request->user()->Id)->select('t_ScheduleUsers.ScheduleId'))
                    ->whereBetween('t_Schedule.StartOn', [$start, $end])
                    ->lock('WITH(NOLOCK)')->withTrashed()->get()
            );
        }


        return view('crm.schedule.index')
            ->with('rooms', MeetingRoom::all(['RoomID', 'Name', 'Capacity']));
        //->with('branches', BranchDetails::all());
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(ScheduleRequest $request): JsonResponse
    {
        $clients = $request->getClients();
        $start = $request->getStart();
        $end = $request->getEnd($start);
        $actor = $request->user();
        if ($request->_type === 'call') {
            $client = $clients->first();
            if (!$client instanceof Client) {//'ClientID', 'Name' can check set and not null
                throw ValidationException::withMessages(['client' => 'Client Not Found']);
            }
            try {
                $schedule = DB::transaction(static function () use ($end, $actor, $client, $start, $request) {
                    return ScheduleService::callSchedule($client, $start, $end, now(), $actor, $request->notes)->schedule;
                });
            } catch (Exception $e) {
                Log::error('Error scheduling call failed:  ' . $e->getMessage());
                return $this->errored('unexpected error scheduling call, try again latter');
            }

            return $this->succeeded('schedule created', data: ['event' => new ScheduleResource($schedule)]);
        }

        if ($request->_type === 'meeting') {
            try {
                $schedule = DB::transaction(static function () use ($end, $actor, $clients, $start, $request) {
                    $meeting = Meeting::create([
                        'Title' => $request->schedule_title,
                        'StartOn' => $start,
                        'EndOn' => $end,
                        'Location' => $request->schedule_location,
                        'Notes' => $request->notes,
                        'CreatedBy' => $actor->Id,
                        'ModifiedBy' => $actor->Id,
                    ]);

                    $description = 'Meeting: ' . $meeting->Title . " - ";
                    $description .= (filter_var($meeting->Location, FILTER_VALIDATE_URL)) ? "Online" : $meeting->Location;
                    return ScheduleService::meetingSchedule($meeting, $clients->toArray(), $start, $end, now(), $actor, $description)->schedule;
                });
            } catch (Exception $e) {
                Log::error('Error scheduling meeting failed:  ' . $e->getMessage());
                return $this->errored('unexpected error scheduling meeting, try again latter');
            }

            return $this->succeeded('schedule created', data: ['event' => new ScheduleResource($schedule)]);
        }

        return $this->errored('could not schedule ' . $request->_type);
    }

    /**
     * Update the specified resource in storage.
     */
   public function show($schedule_id): JsonResponse|View
{
    $schedule = Schedule::query()->where('ScheduleID', $schedule_id)->withTrashed()->first();
    if (!$schedule instanceof Schedule) {
        return $this->errored('schedule not found');
    }

    $scheduleService = new ScheduleService($schedule);

    if ($schedule->ScheduledType === Call::getPrimaryKey()) {
        if ($schedule->Type === Client::getPrimaryKey()) {
            $party = $scheduleService->clients()->first();
            if (!$party instanceof Client) {
                return $this->errored('call party not found');
            }
        } elseif ($schedule->Type === Lead::getPrimaryKey()) {
            $party = $scheduleService->leads()->first();
            if (!$party instanceof Lead) {
                return $this->errored('call party not found');
            }
        } else {
            return $this->errored('schedule party not found');
        }

        $call = $schedule->scheduled;

        if (!$call || !$call->StartOn || !$call->EndOn) {
            \Log::warning("Incomplete call details for ScheduleID {$schedule->ScheduleID}");
            $call = null; 
        }

        return view('crm.schedule.call')
            ->with('schedule', $schedule)
            ->with('call', $call)
            ->with('service', $scheduleService)
            ->with('party', $party);
    }

    if ($schedule->ScheduledType === Meeting::getPrimaryKey()) {
        $parties_count = 0;

        if ($schedule->Type === Client::getPrimaryKey()) {
            $parties_count = $scheduleService->clientsCount();
            if ($parties_count === 1) {
                $parties = $scheduleService->clients()->first();
                if (!$parties instanceof Client) {
                    return $this->errored('appointment party not found');
                }
            } else {
                $parties = $scheduleService->clients(['photo'], 10)->paginate(7, ['ClientID', 'Name', 'PhotoID']);
            }

        } elseif ($schedule->Type === User::getPrimaryKey()) {
            $parties_count = $schedule->users()->count();
            if ($parties_count === 1) {
                $parties = $scheduleService->users()->first();
                if (!$parties instanceof User) {
                    return $this->errored('appointment party not found');
                }
            } else {
                $parties = $scheduleService->users()->limit(10)->paginate(7);
            }

        } elseif ($schedule->Type === Lead::getPrimaryKey()) {
            $parties = $scheduleService->leads()->first();
            if (!$parties instanceof Lead) {
                return $this->errored('appointment party not found');
            }

        } elseif ($schedule->Type === Board::getPrimaryKey()) {
            $parties_count = $scheduleService->boardsCount();
            if ($parties_count === 1) {
                $parties = $scheduleService->boards()->first();
                if (!$parties) {
                    return $this->errored('appointment party not found');
                }
            } else {
                $parties = $scheduleService->boards()->paginate(7);
            }

        } else {
            return $this->errored('schedule party not found');
        }

        return view('crm.schedule.meeting')
            ->with('schedule', $schedule)
            ->with('meeting', $schedule->scheduled)
            ->with('service', $scheduleService)
            ->with('parties', $parties)
            ->with('parties_count', ($parties_count - 7));
    }

    return $this->errored('unknown schedule type');
}


    /**
     * Update the specified resource in storage.
     * @throws ValidationException
     */
    public function update(ScheduleRequest $request, $schedule_id): JsonResponse
    {
        $schedule = Schedule::query()->where('ScheduleID', $schedule_id)->first();
        if (!$schedule instanceof Schedule) {
            return $this->errored('schedule not found');
        }

        if ($schedule->ScheduledType !== Call::getPrimaryKey() || !(new ScheduleService($schedule))->editable()) {
            return $this->errored('schedule cannot be edited');
        }

        $start = $request->getStart();
        $end = $request->getEnd($start);
        $user = $request->user();

        $schedule->update([
            'ModifiedBy' => $user->Id,
            'ModifiedOn' => now(),
            'StartOn' => $start,
            'EndOn' => $end,
        ]);

        return $this->succeeded('updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $schedule_id): JsonResponse
    {
        return $this->succeeded('deprecated use the other one');
    }
}
