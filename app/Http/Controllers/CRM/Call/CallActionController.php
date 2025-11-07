<?php

namespace App\Http\Controllers\CRM\Call;

use App\Enums\CallStatusEnum;
use App\Enums\CallTypeEnum;
use App\Enums\ScheduleStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Call\StartCallNewContactRequest;
use App\Models\Communication\Call;
use App\Models\CRM\Contact;
use App\Models\CRM\DiscussionUser;
use App\Models\CRM\Notes;
use App\Models\CRM\Schedule;
use App\Services\ActivityService;
use App\Services\Call\CallService;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CallActionController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * @throws ValidationException
     */
    public function reschedule(Request $request, Schedule $schedule): JsonResponse
    {
        $request->validate([
            'reschedule_start' => ['required', 'date_format:"H:i"'],
            'schedule_start' => ['required', 'date_format:"Y-m-d H:i"'],
            'schedule_discussion' => ['required', 'min:5', 'max:5000'],
            'schedule_notes' => ['nullable', 'min:5', 'max:5000'],
        ]);

        $start = Carbon::createFromFormat('Y-m-d H:i', $request->input('schedule_start'));
        if (!$start instanceof Carbon) {
            throw ValidationException::withMessages(['schedule_start' => 'invalid date format']);
        }

        if ($start->lt(Carbon::now()->addMinutes(50))) {
            throw ValidationException::withMessages(['schedule_start' => 'schedule within the hour']);
        }

        $current_start = Carbon::createFromFormat('H:i', $request->input('reschedule_start'));
        if (!$current_start instanceof Carbon) {
            throw ValidationException::withMessages(['reschedule_start' => 'invalid date format']);
        }

        if ($current_start->greaterThan(now())) {
            $current_start = now()->subMinute();
        }

        $actor = $request->user();
        $party = $schedule->resolveParty();

        try {
            DB::transaction(static function () use ($current_start, $actor, $start, $schedule, $request, $party) {
                $duration = $schedule->StartOn->diffInMinutes($schedule->EndOn, true);
                $dated = now();

                $callID = Call::insertGetId([
                    'ScheduleID' => $schedule->ScheduleID,
                    'Party' => $party['party'],
                    'PartyID' => $party['party_id'],
                    'UserID' => $actor->Id,
                    'StartOn' => $current_start,
                    'EndOn' => $current_start->copy()->addSeconds(5),
                    'CallStatusID' => CallStatusEnum::SuccessReschedule,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                    'CreatedOn' => $dated,
                    'ModifiedOn' => $dated,
                ]);
                $call = Call::findOrFail($callID);

                $schedule->update([
                    'ScheduleStatusID' => ScheduleStatusEnum::Success,
                    'ScheduledTypeID' => $callID,
                ]);

                $discussionID = DB::table('t_Discussions')->insertGetId([
                    'SourceType' => Call::getPrimaryKey(),
                    'SourceTypeID' => $call->CallID,
                    'Party' => $party['party'],
                    'PartyID' => $party['party_id'],
                    'Discussion' => $request->schedule_discussion,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                    'CreatedOn' => $current_start,
                    'ModifiedOn' => $current_start,
                ]);

                DiscussionUser::create([
                    'DiscussionId' => $discussionID,
                    'UserID' => $actor->Id,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                    'CreatedOn' => $dated,
                    'ModifiedOn' => $dated,
                ]);

                ActivityService::call($call, 'Scheduled Call By ' . $actor->UserID . '. requested reschedule', $actor);

                ScheduleService::callSchedule($call->party, $start, $start->copy()->addMinutes($duration), $current_start, $actor, '', true);

                if (is_string($request->schedule_notes)) {
                    Notes::create([
                        'DiscussionID' => $discussionID,
                        'Party' => $party['party'],
                        'PartyID' => $party['party_id'],
                        'Notes' => $request->schedule_notes,
                        'CreatedBy' => $actor->Id,
                        'ModifiedBy' => $actor->Id,
                    ]);
                }
            });
        } catch (Exception $e) {
            Log::error('Error rescheduling call ' . $e->getMessage());
            return $this->errored('unexpected error rescheduling, try again later');
        }

        return $this->succeeded('call rescheduled', route('schedule.index'));
    }

    /**
     * @throws ValidationException
     */
    public function unreachable(Request $request, Schedule $schedule): JsonResponse
    {
        $request->validate([
            'unreachable_start' => ['required', 'date_format:"H:i"'],
            'type' => ['required', Rule::in(CallStatusEnum::unreachable()->values()->toArray())],
            'unreachable_comment' => ['nullable', 'string', 'max:255'],
        ]);

        $start = Carbon::createFromFormat('H:i', $request->input('unreachable_start'));
        if (!$start instanceof Carbon) {
            throw ValidationException::withMessages(['unreachable_start' => 'invalid time format']);
        }

        if ($start->greaterThan(now())) {
            $start = now()->subMinute();
        }

        $actor = $request->user();
        $party = $schedule->resolveParty();

        try {
            DB::transaction(static function () use ($actor, $start, $schedule, $request, $party) {
                $dated = now();

                $callID = Call::insertGetId([
                    'ScheduleID' => $schedule->ScheduleID,
                    'Party' => $party['party'],
                    'PartyID' => $party['party_id'],
                    'UserID' => $actor->Id,
                    'StartOn' => $start,
                    'EndOn' => $start->copy()->addSeconds(5),
                    'Notes' => $request->unreachable_comment,
                    'CallStatusID' => $request->type,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                    'CreatedOn' => $dated,
                    'ModifiedOn' => $dated,
                ]);

                $schedule->update([
                    'ScheduleStatusID' => ScheduleStatusEnum::PartialSuccess,
                    'ScheduledTypeID' => $callID,
                ]);

                ActivityService::call(Call::findOrFail($callID), 'Failed Scheduled Call By ' . $actor->UserID, $actor);
            });
        } catch (Exception $e) {
            Log::error('Error unreachable call ' . $e->getMessage());
            return $this->errored('unexpected error marking unreachable, try again later');
        }

        return $this->succeeded('Call marked as unreachable');
    }

    public function startWithContact(StartCallNewContactRequest $request): JsonResponse
    {
        $current_start = $request->getStart();
        $actor = $request->user();

        try {
            $combo = DB::transaction(static function () use ($current_start, $request, $actor) {
                $contact = Contact::where('Phone', $request->validated('Phone'))->first();

                if (!$contact instanceof Contact) {
                    $contact = Contact::create([
                        'Label' => $request->validated('Name'),
                        'Phone' => $request->validated('Phone'),
                        'Party' => Contact::getPrimaryKey(),
                        'PartyID' => 0,
                        'CreatedBy' => $actor->Id,
                        'ModifiedBy' => $actor->Id,
                    ]);
                }

                $call = CallService::createContact($contact, CallStatusEnum::SuccessOngoing, CallTypeEnum::Incoming, $current_start, $actor)->call;

                return [
                    'call' => $call,
                    'contact' => $contact,
                ];
            });
        } catch (Exception $e) {
            Log::error('Error starting call ' . $e->getMessage());
            return $this->errored('unexpected error starting call, try again later');
        }

        return $this->succeeded('start a call', route('unattached.contacts.show', [$combo['contact']->ContactID, 'call' => $combo['call']->CallID]));
    }
}
