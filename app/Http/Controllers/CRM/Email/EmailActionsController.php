<?php

namespace App\Http\Controllers\CRM\Email;

use App\Enums\EmailStatusEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Base\UploadDocumentRequest;
use App\Http\Requests\Email\SendDraftMailRequest;
use App\Models\CrmEmail;
use App\Services\CRMEmailService;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmailActionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     */
    public function attachment(UploadDocumentRequest $request, string $email_id): JsonResponse
    {
        //todo check permissions
        $crmEmail = CrmEmail::query()->where('EmailID', $email_id)
            ->where('Status', EmailStatusEnum::Draft->value)->where('CreatedBy', $request->user()->Id)->first();
        if (!$crmEmail instanceof CrmEmail) {
            return $this->errored('could not load email');
        }

        try {
            $document = DB::transaction(static function () use ($crmEmail, $request) {
                $service = ImageService::createUpload($request->file('file'), CrmEmail::getPrimaryKey(), $crmEmail->EmailID, $request->user());
                (new CRMEmailService($crmEmail))->addAttachment($service->image);
                return $service->image;
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (\Exception | \Throwable $e) {
            Log::error('Error upload email attachment : ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('attachment added', data: [
                                                           'html' => (new ImageService($document))->summaryList(),
                                                          ]);
    }


    public function send(SendDraftMailRequest $request, string $email_id): JsonResponse
    {
        $crmEmail = CrmEmail::query()->where('EmailID', $email_id)
            ->where('Status', EmailStatusEnum::Draft->value)->where('CreatedBy', $request->user()->Id)->first();
        if (!$crmEmail instanceof CrmEmail) {
            return $this->errored('could not load email');
        }

        $cc = $request->getCarbonCopyEmails();
        try {
            DB::transaction(static function () use ($cc, $crmEmail, $request) {
                $crmEmail->update([
                                   'Body' => $request->validated('mail_content'),
                                   'CC'   => $cc,
                                  ]);
                (new CRMEmailService($crmEmail))->send();
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (\Exception | \Throwable $e) {
            Log::error($e);
            Log::error('Error send draft email : ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('sending email', route: route('email-conversations.show', [$crmEmail->EmailConversationId]));
    }
}
