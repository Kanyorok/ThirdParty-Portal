<?php

namespace App\Http\Controllers\CRM\Email;

use App\Enums\EmailStatusEnum;
use App\Enums\EmailTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Base\MailToRequest;
use App\Models\CrmEmail;
use App\Services\CRMEmailService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmailDraftController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     */
    public function reply(Request $request, string $email_id): JsonResponse|View
    {
        //todo permissions
        $lock = Cache::lock($email_id . '-create-draft', 5);
        if ($lock->get()) {
            $replyTo = CrmEmail::query()->where('EmailID', $email_id)
                ->where('Type', EmailTypeEnum::Incoming->value)->first();
            if (!$replyTo instanceof CrmEmail) {
                $lock->release();
                return $this->errored('could not load email');
            }

            $subject = $replyTo->Subject;
            if (!Str::startsWith($subject, 'Re:')) {
                $subject = 'Re: ' . $subject;
            }
            try {
                $CrmEmail = DB::transaction(static function () use ($subject, $replyTo, $request) {
                    $CrmEmail = (new CRMEmailService($replyTo))
                        ->createReply($replyTo->From, $subject, "<br><hr>" . $replyTo->Body, $request->user(), $replyTo->CC ?? [])->crmEmail;
                    $CrmEmail->conversation?->increment('Emails');
                    return $CrmEmail;
                });
            } catch (Exception | \Throwable $e) {
                Log::error('Error saving draft ' . $e->getMessage());
                return $this->errored('error creating draft');
            }

            return $this->edit($request, $CrmEmail->EmailID);
        }
        return $this->errored('similar email is being edited by another user');
    }

    public function edit(Request $request, string $email_id): View|JsonResponse
    {
        $email = CrmEmail::query()->where('EmailID', $email_id)
            ->where('Status', EmailStatusEnum::Draft->value)->where('CreatedBy', $request->user()->Id)->first();
        if (!$email instanceof CrmEmail) {
            return $this->errored('could not load email');
        }

        return view('crm.emails.conversations.edit', compact('email'))->with('service', new CRMEmailService($email));
    }

    /**
     * Handle the incoming request.
     */
    public function new(MailToRequest $request): JsonResponse
    {
        /*$replyTo = $request->getReplyTo();
        $email = $request->getEmail();

        try {
            DB::transaction(static function () use ($replyTo,  $email, $request) {
                (new CRMEmailService($replyTo))->createReply($email, $request->validated('mail_subject'), $request->validated('mail_content'), $request->user(), $request->getCarbonCopyEmails())
                    ->send();
            });
        } catch (Exception|\Throwable $e) {
            Log::error('Error reply email ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('email queued for sending', data: ['summary_url' => route('email-conversations.show', [$replyTo->EmailConversationId])]);*/
        return $this->errored('unexpected error, try again later');
    }
}
