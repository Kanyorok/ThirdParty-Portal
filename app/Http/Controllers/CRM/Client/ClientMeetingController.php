<?php

namespace App\Http\Controllers\CRM\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Call\StartMeetingRequest;
use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\CRM\Meeting;
use App\Models\CRM\Schedule;
use App\Traits\Controller\MeetingTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ClientMeetingController extends Controller
{
    use MeetingTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Client $client): JsonResponse
    {
        return $this->meetings($client->meetings());
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(StartMeetingRequest $request, Client $client): JsonResponse
    {
        $schedule = $request->getSchedule();
        $current_start = $request->getStart();
        $actor = $request->user();
        $meeting = null;
        if ($schedule instanceof Schedule) {
            $meeting = $schedule->scheduled;
            if (!$meeting instanceof Meeting) {
                throw ValidationException::withMessages(['meeting_initiated' => 'invalid schedule provide']);
            }
        }
        try {
            $meeting = $this->startClientMeeting($client, $request->validated('meeting_initiated_title'), $request->validated('meeting_initiated_location'), $current_start, $actor, $meeting, $schedule);
        } catch (Exception $e) {
            Log::error('Error starting meeting  ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('entering meeting', route('clients.show', $client->ClientID) . "?meet=" . $meeting->MeetingID);
    }

    /**
     * Update the specified resource in storage.
     * @throws ValidationException
     */
    public function update(Request $request, Client $client, $meeting_id): JsonResponse
    {
        $request->validate([
                            'ongoing_meeting_title'      => [
                                                             'required',
                                                             'min:5',
                                                             'max:200',
                                                            ],
                            'ongoing_meeting_location'   => [
                                                             'required',
                                                             'min:5',
                                                             'max:200',
                                                            ],
                            'ongoing_meeting_discussion' => [
                                                             'required',
                                                             'min:5',
                                                             'max:5000',
                                                            ],
                            'meeting_notes'              => [
                                                             'nullable',
                                                             'max:5000',
                                                            ],
                            'ongoing_meeting_users'      => [
                                                             'required',
                                                             'array',
                                                             'min:1',
                                                             'max:200',
                                                            ],
                           ]);

        $meeting = $client->meetings()->where('t_Meetings.MeetingID', $meeting_id)->first();
        if (!$meeting instanceof Meeting) {
            return $this->errored('unexpected error saving, with meeting');
        }
        $userIds = User::query()->whereIn('t_Users.UserID', $request->get('ongoing_meeting_users'))->pluck('Id')->toArray();
        if (count($userIds) === 0) {
            throw ValidationException::withMessages(['users' => "select some attendees"]);
        }

        $actor = $request->user();

        try {
            $this->endMeeting($meeting, $request->ongoing_meeting_title, $request->ongoing_meeting_location, $client->ClientID, Carbon::now()->subSeconds(3), $actor, $request->ongoing_meeting_discussion, $userIds, $request->ongoing_meeting_notes);
        } catch (Exception $e) {
            Log::error('Error end Meeting ' . $e->getMessage());
            return $this->errored('unexpected error saving, try again latter');
        }

        return $this->succeeded('meeting ended successful', route('clients.show', [$client->ClientID]));
    }
}
