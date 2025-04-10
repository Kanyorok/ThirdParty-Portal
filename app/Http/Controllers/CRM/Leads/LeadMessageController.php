<?php

namespace App\Http\Controllers\CRM\Leads;

use App\Http\Controllers\Controller;
use App\Http\Requests\Base\MessageRequest;
use App\Models\Lead;
use App\Services\SMSService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LeadMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }


    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Lead $lead): JsonResponse
    {
        return SMSService::dt($lead->crmsms(), ['source']);
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(MessageRequest $request, Lead $lead): JsonResponse
    {

        $phone = $request->getLeadPhone($lead);

        try {
            $activity = DB::transaction(static function () use ($phone, $lead, $request) {
                $service = SMSService::createLead($lead, $request->validated('message_content'), $request->user(), $phone);
                $activity = $service->addActivity(now());
                $service->send();
                return $activity;
            });
        } catch (Exception $e) {
            Log::error('Error sending sms to lead ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('sending message', data: ['activity' => $activity]);
    }
}
