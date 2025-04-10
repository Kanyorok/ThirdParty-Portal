<?php

namespace App\Http\Controllers\CRM\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Base\MailToRequest;
use App\Models\Contact;
use App\Services\CRMEmailService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ContactEmailController extends Controller
{
    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Contact $contact): JsonResponse
    {
        return CRMEmailService::dt($contact->crmmails());
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(MailToRequest $request, Contact $contact): JsonResponse
    {
        $email = $request->getContactEmail($contact);
        $cc = $request->getCarbonCopyEmails();
        try {
            DB::transaction(static function () use ($cc, $email, $contact, $request) {
                CRMEmailService::createContact($contact, $email, $request->validated('mail_subject'), $request->validated('mail_content'), $request->user(), $cc)
                ->send();
            });
        } catch (Exception|\Throwable $e) {
            Log::error('Error sending email to client ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('email sent successfully');
    }
}
