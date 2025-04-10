<?php

namespace App\Http\Controllers\Email;

use App\Enums\EmailStatusEnum;
use App\Enums\EmailTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Base\MailToRequest;
use App\Models\CrmEmail;
use App\Models\EmailConversation;
use App\Services\CRMEmailService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RuntimeException;

class CrmEmailController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->except(['index']);
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $actor = $request->user();
            if ($request->_filter === 'DRAFTS') {
                $query = CrmEmail::query()->where('Type', EmailTypeEnum::Outgoing->value)->where('Status', EmailStatusEnum::Draft->value)
                    ->where('CreatedBy', $actor->Id);
            } elseif ($request->_filter === 'SENT') {
                $query = CrmEmail::query()->where('Type', EmailTypeEnum::Outgoing->value)->whereIn('Status', [EmailStatusEnum::Queued->value, EmailStatusEnum::Sending->value, EmailStatusEnum::Sent->value, EmailStatusEnum::Failed->value,])
                ->where('CreatedBy', $actor->Id);
            } else {
                throw new RuntimeException("Unexpected error");
            }
            return CRMEmailService::dt($query, ['party']);
        }

        return view('emails.index');
    }

    public function store(MailToRequest $request): JsonResponse
    {
        $replyTo = $request->getReplyTo();
        $email = $request->getEmail();

        try {
            DB::transaction(static function () use ($replyTo, $email, $request) {
                (new CRMEmailService($replyTo))->createReply($email, $request->validated('mail_subject'), $request->validated('mail_content'), $request->user(), $request->getCarbonCopyEmails())
                    ->send();
            });
        } catch (Exception|\Throwable $e) {
            Log::error('Error reply email ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('email queued for sending', data: ['summary_url' => route('email-conversations.show', [$replyTo->EmailConversationId])]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $email_id): JsonResponse|View
    {
        $crmEmail = CrmEmail::query()->where('EmailID', $email_id)->first();
        if (!$crmEmail instanceof CrmEmail) {
            return $this->errored('could not load email');
        }

        return view('emails.show', compact('crmEmail'))
            ->with('party', $crmEmail->party);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function summary(string $email_id): JsonResponse|View
    {
        $crmEmail = CrmEmail::query()->where('EmailID', $email_id)->first();
        if (!$crmEmail instanceof CrmEmail) {
            return $this->errored('could not load email');
        }

        return view('emails.summary', compact('crmEmail'))
            ->with('party', $crmEmail->party);
    }

    /**
     * Mark Email as Read
     */
    public function update(Request $request, string $email_id): JsonResponse
    {
        //todo check permissions
        $crmEmail = CrmEmail::query()->where('EmailID', $email_id)->first();
        if ($crmEmail instanceof CrmEmail) {
            try {
                DB::transaction(static function () use ($crmEmail, $request) {
                    $crmEmail->update([
                        'Status' => EmailStatusEnum::Read->value,
                        'ReadBy' => $request->user()->Id,
                        'ReadOn' => now()
                    ]);

                    activity()->by($request->user())->on($crmEmail)->event('mark read')->log('marked email (' . $crmEmail?->MailID . ') as read');

                });
            } catch (Exception|\Throwable $e) {
                Log::error('Error Marking email as read :  ');
                Log::error($e);
            }

        }
        return $this->succeeded('Email marked as read', data: ['email_id' => $email_id, 'status' => 'read']);
    }

    public function destroy(Request $request, string $email_id): JsonResponse
    {
        //todo check permissions
        $crmEmail = CrmEmail::query()->where('EmailID', $email_id)->first();
        if (!$crmEmail instanceof CrmEmail) {
            return $this->errored('could not load email');
        }
        try {
            $conversation = DB::transaction(static function () use ($crmEmail, $request) {
                $conversation = false;
                $crmEmail->forceFill([
                    'DeletedBy' => $request->user()->Id,
                    'DeletedOn' => now()
                ])->save();
                $crmEmail->attachments()->delete();

                if ($crmEmail->conversation instanceof EmailConversation) {
                    if ($crmEmail->conversation->Emails === 1) {
                        $crmEmail->conversation->forceFill([
                            'DeletedBy' => $request->user()->Id,
                            'DeletedOn' => now()
                        ])->save();
                        $conversation = true;
                    } else {
                        $crmEmail->conversation->decrement('Emails');
                    }
                }
                $description = "Deleted email ";

                activity()->by($request->user())->on($crmEmail)->event('delete')->log($description . ($crmEmail->Type === EmailTypeEnum::Incoming->value) ? " received from $crmEmail->From" : " sent by " . $crmEmail->creator->UserID);
                return $conversation;
            });
        } catch (Exception|\Throwable $e) {
            Log::error('Error Marking email as read :  ');
            Log::error($e);
            $conversation = false;
        }

        return $this->succeeded('Email deleted Successfully', route: route('email-conversations.index'), data: ['email_id' => ($crmEmail->Status->value === EmailStatusEnum::Draft->value) ? 'mailReplyContactForm' : $email_id, 'conversation' => $conversation]);
    }
}
