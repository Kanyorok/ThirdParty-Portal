<?php

namespace App\Http\Controllers\CRM\Leads;

use App\Http\Controllers\Controller;
use App\Http\Requests\Base\MailToRequest;
use App\Models\Lead;
use App\Services\CRMEmailService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LeadEmailController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Sent and Received Emails
     * @throws Exception
     */
    public function index(Lead $lead): JsonResponse
    {
        return CRMEmailService::dt($lead->crmmails());
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(MailToRequest $request, Lead $lead): JsonResponse
    {
        $email = $request->getLeadEmail($lead);
        //  $cc = array_merge($request->cc(), $request->getUsers());
        try {
            $activity = DB::transaction(static function () use ($email, $lead, $request) {
                $service = CRMEmailService::createLead($lead, $email, $request->validated('mail_subject'), $request->validated('mail_content'), $request->user(), $request->getCarbonCopyEmails());
                $activity = $service->addActivity(now());
                $service->send();
                return $activity;
            });
        } catch (Exception | \Throwable $e) {
            Log::error('Error sending email to lead ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('sending email', data: ['activity' => $activity]);
    }
}
