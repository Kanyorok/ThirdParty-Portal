<?php

namespace App\Http\Controllers\CRM\Leads;

use App\Http\Controllers\Controller;
use App\Http\Requests\Base\MailToRequest;
use App\Models\Communication\Email;
use App\Models\CRM\Lead;
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
        $this->middleware('ajax')->except(['index']);
    }

    /**
     * Display list of lead emails (sent & received).
     * @throws Exception
     */
    public function index(Lead $lead): JsonResponse
    {
        return CRMEmailService::dt($lead->crmmails());
    }

    /**
     * Store a newly created email.
     * @throws ValidationException
     */
    public function store(MailToRequest $request, Lead $lead): JsonResponse
    {
        $email = $request->getLeadEmail($lead);

        try {
            $activity = DB::transaction(static function () use ($email, $lead, $request) {
                $service = CRMEmailService::createLead(
                    $lead,
                    $email,
                    $request->validated('mail_subject'),
                    $request->validated('mail_content'),
                    $request->user(),
                    $request->getCarbonCopyEmails()
                );

                $activity = $service->addActivity(now());
                $service->send();

                return $activity;
            });
        } catch (Exception | \Throwable $e) {
            Log::error('Error sending email to lead: ' . $e->getMessage());

            return $this->errored('Unexpected error, try again later.');
        }

        return $this->succeeded('Email sent successfully', data: ['activity' => $activity]);
    }

    public function show(Lead $lead, Email $leadMail)
    {
        if (! $lead->crmmails()->where('EmailID', $leadMail->EmailID)->exists()) {
            return $this->errored('invalid email');
        }

        return view('crm.emails.summary', [
            'party' => $lead,
            'crmEmail' => $leadMail,
        ]);
    }
}
