<?php

namespace App\Http\Controllers\CRM\Leads;

use App\Enums\CallStatusEnum;
use App\Enums\CallTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Call\StartCallRequest;
use App\Models\Call;
use App\Models\Lead;
use App\Services\Call\CallService;
use App\Traits\Controller\CallsTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LeadCallController extends Controller
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
    public function index(Lead $lead): JsonResponse
    {
        return $this->calls($lead->calls());
    }

    /**
     * Start a lead call
     * @throws ValidationException
     */
    public function store(StartCallRequest $request, Lead $lead): JsonResponse
    {
        $schedule = $request->getSchedule();
        $current_start = $request->getStart();
        $actor = $request->user();

        try {
            $call = DB::transaction(static function () use ($lead, $current_start, $actor, $schedule) {
                return CallService::createLead($lead, CallStatusEnum::SuccessOngoing,CallTypeEnum::Incoming, $current_start, $actor, $schedule)->call;
            });
        } catch (\Throwable|Exception $e) {
            Log::error('Error starting call ' . $e->getMessage());
            return $this->errored('unexpected error start call, try again latter');
        }

        return $this->succeeded('start a call', route('leads.show', $lead->LeadID) . "?call=" . $call->CallID);
    }

    /**
     * Call Details
     */
    public function show(Request $request, Lead $lead, Call $call): JsonResponse
    {
        return $this->errored('an error occurred');
    }

    /**
     * Update the specified resource in storage.
     * @throws ValidationException
     */
    public function update(Request $request, Lead $lead, int $callID): JsonResponse
    {
        $request->validate([
            'call_discussion' => ['required', 'min:5', 'max:5000'],
            'private_notes' => ['nullable', 'max:5000'],
        ]);

        $call = $lead->calls()->where('t_Calls.CallID', $callID)->first();
        if (!$call instanceof Call) {
            throw ValidationException::withMessages([
                'call_discussion' => 'call selected could have been deleted.'
            ]);
        }

        $actor = $request->user();

        try {
            $this->endCall($call, Carbon::now()->subSeconds(3), $actor, $request->call_discussion, $request->private_notes);
        } catch (\Throwable|Exception $e) {
            Log::error('Error call ' . $e->getMessage());
            return $this->errored('unexpected error saving, try again latter');
        }

        return $this->succeeded('call successful', route('leads.show', [$lead->LeadID]));
    }
}
