<?php

namespace App\Http\Controllers\CRM\Contact;

use App\Enums\CallStatusEnum;
use App\Enums\CallTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Call\StartCallRequest;
use App\Models\Call;
use App\Models\Contact;
use App\Services\Call\CallService;
use App\Traits\Controller\CallsTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ContactCallController extends Controller
{
    use CallsTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     * @throws Exception
     */
    public function index(Contact $contact): JsonResponse
    {
        return $this->calls($contact->calls());
    }

    /**
     * Start a call
     * @throws ValidationException
     */
    public function store(StartCallRequest $request, Contact $contact): JsonResponse
    {
        $schedule = $request->getSchedule();
        $current_start = $request->getStart();
        $actor = $request->user();

        try {
            $call = DB::transaction(static function () use ($contact, $current_start, $actor, $schedule) {
                return CallService::createContact($contact, CallStatusEnum::SuccessOngoing,CallTypeEnum::Incoming, $current_start, $actor, $schedule)->call;
            });
        } catch (\Throwable|Exception $e) {
            Log::error('Error starting call ' . $e->getMessage());
            return $this->errored('unexpected error start call, try again latter');
        }

        return $this->succeeded('call started, redirecting', route('unattached.contacts.show', $contact->ContactID,['call'=>$call->CallID]));
    }

    /**
     * Call Details
     */
    public function show(Request $request,  Contact $contact, Call $call): JsonResponse
    {
        return $this->errored('an error occurred');
    }

    /**
     * Update the specified resource in storage.
     * @throws ValidationException
     */
    public function update(Request $request,  Contact $contact, int $callID): JsonResponse
    {
        $request->validate([
            'call_discussion' => ['required', 'min:5', 'max:5000'],
            'private_notes' => ['nullable', 'max:5000'],
        ]);

        $call = $contact->calls()->where('t_Calls.CallID', $callID)->first();
        if (!$call instanceof Call) {
            throw ValidationException::withMessages([
                'call_discussion' => 'call selected could have been deleted.'
            ]);
        }

        $actor = $request->user();

        try {
            $this->endCall($call, Carbon::now()->subSeconds(3), $actor, $request->call_discussion, $request->private_notes, false);
        } catch (\Throwable|Exception $e) {
            Log::error('Error call ' . $e->getMessage());
            Log::error($e);
            return $this->errored('unexpected error saving, try again latter');
        }

        return $this->succeeded('call successful', route('unattached.contacts.show', [$contact->ContactID]));
    }
}
