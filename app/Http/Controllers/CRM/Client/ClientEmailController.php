<?php

namespace App\Http\Controllers\CRM\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Base\MailToRequest;
use App\Models\BR\Client;
use App\Services\CRMEmailService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ClientEmailController extends Controller
{
    /**
     * Sent and Received Emails
     * @throws Exception
     */
    public function index(Client $client): JsonResponse
    {
        return CRMEmailService::dt($client->crmmails());
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(MailToRequest $request, Client $client): JsonResponse
    {
        $email = $request->getClientEmail($client);
        $cc = $request->getCarbonCopyEmails();
        try {
            $activity = DB::transaction(static function () use ($cc, $email, $client, $request) {
                $service = CRMEmailService::createClient($client, $email, $request->validated('mail_subject'), $request->validated('mail_content'), $request->user(), $cc);
                $activity = $service->addActivity(now());
                $service->send();
                return $activity;
            });
        } catch (Exception | \Throwable $e) {
            Log::error('Error sending email to client ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('email sent successfully', data: ['activity' => $activity]);
    }
}
