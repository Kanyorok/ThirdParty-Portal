<?php

namespace App\Console\Commands;

use App\Enums\Core\IntegrationsEnum;
use App\Enums\EmailPriorityEnum;
use App\Enums\EmailStatusEnum;
use App\Enums\EmailTypeEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\Communication\Email;
use App\Models\Settings\APICredential;
use App\Services\CRMEmailService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpImap\Exceptions\ConnectionException;
use PhpImap\Exceptions\InvalidParameterException;
use PhpImap\Mailbox;

class CheckEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-email-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $actor = SystemHelper::user();
        try {
            $email = APICredential::query()->where('Integration', IntegrationsEnum::Email->value)->latest('Id')->first();
            if (!$email instanceof APICredential) {
                throw new ErroredException('no email mail configuration');
            }
            $emailConfig = $email->Configuration;

            $mailbox = new Mailbox(// 993 crm@imarishasacco.co.ke SSL
            //'{' . config('mail.incoming.host') . ':' . config('mail.incoming.port') . '/' . config('mail.incoming.transport') . '/' . config('mail.incoming.encryption') . '}' . config('mail.incoming.folder'),
                '{' . $emailConfig?->Incoming?->host . ':' . $emailConfig?->Incoming?->port . '/imap/' . $emailConfig?->Incoming?->encryption . '}' . $emailConfig?->Incoming?->folder,
                $emailConfig?->Incoming?->username, // Username for the before configured mailbox
                $emailConfig?->Incoming?->password, // Password for the before configured username
                null,
                "US-ASCII"//'UTF-8',
            );//SE_UID, "US-ASCII")
        } catch (ConnectionException | InvalidParameterException | Exception $e) {
            SystemHelper::notifyAdmin('Issue with fetch mail: ' . $e->getMessage());
            return;
        }

        //  dd($mailbox->checkMailbox());
        //dd($mailbox->getMailboxInfo());
        //dd($mailbox->getMailboxes());
        // dd($mailbox->searchMailbox('UNSEEN'));

        // dd($mailbox->getMail(41, false));

        foreach ($mailbox->searchMailbox('UNSEEN') as $emailID) {
            try {
                $email = $mailbox->getMail($emailID, false);
            } catch (Exception) {
                continue;
            }
            try {
                $dated = Carbon::parse($email->date);
            } catch (Exception $e) {
                SystemHelper::notifyAdmin('Issue with fetch mail ' . $emailID . ' date: ' . $e->getMessage());
                continue;
            }

            $ref = null;
            if (is_object($email->headers) && property_exists($email->headers, 'in_reply_to')) {
                $ref = trim($email->headers->in_reply_to, '<>');
            }
            $messageID = trim($email->messageId ?? '', '<>');
            if (empty($messageID)) {
                continue;
            }
            if (Email::query()->where('MailID', $messageID)->exists()) {
                continue;
            }
            try {
                DB::transaction(function () use ($ref, $dated, $email, $actor) {

                    $crmEmail = new Email();
                    $crmEmail->fill([
                                     'MailID'      => trim($email->messageId ?? '', '<>'),
                                     'Type'        => EmailTypeEnum::Incoming->value,
                                     'Status'      => EmailStatusEnum::Unread->value,
                                     'Priority'    => $this->priority($email->priority, $email->importance)->value,
                                     'From'        => $email->fromAddress,
                                     'To'          => $this->_flipAddresses($email->to),
                                     'CC'          => $this->_flipAddresses($email->cc),
                                     'ReferenceId' => $ref,
                                     'Subject'     => $email->subject,
                                     'Body'        => (empty($email->textHtml)) ? $email->textPlain : $this->_parserHtml($email->textHtml),
                                     'Text'        => Str::of($email->textPlain)->trim()->value(),
                                     'CreatedBy'   => $actor->Id,
                                     'ModifiedBy'  => $actor->Id,
                                     'Dated'       => $dated,
                                    ])->save();

                    $service = (new CRMEmailService($crmEmail->refresh()))->autoAttachIncoming($dated);
                    if ($email->hasAttachments()) {
                        foreach ($email->getAttachments() as $attachment) {
                            $service->addAttachmentContent($attachment->getContents(), $attachment->mimeType ?? '', $attachment->name ?? '', $actor);
                            /*  if (in_array($attachment->mimeType, ['image/png', 'image/jpeg', 'image/jpeg', 'image/jpeg', 'image/gif', 'image/bmp'], true)) {
                                  ImageService::create('')
                                  $imgID = Image::insertGetId([
                                      "ImageType" => Email::getPrimaryKey(),
                                      "ImageTypeID" => $crmEmail->EmailID,
                                      "Image" => base64_encode(),
                                      "MIMEType" => $attachment->mimeType,
                                      'CreatedBy' => $actor->Id,
                                      'ModifiedBy' => $actor->Id,
                                      'CreatedOn' => $dated,
                                      'ModifiedOn' => $dated,
                                  ]);
                                  $crmEmail->attachments()->attach($imgID, [], false);
                              }*/
                        }
                    }
                });
            } catch (Exception | \Throwable $e) {
                SystemHelper::notifyAdmin('handle incoming email failed: ' . $e->getMessage());
                continue;
            }


            //mark as read.
            try {
                $mailbox->markMailAsRead($emailID);
            } catch (Exception $e) {
            }
        }

        $mailbox->disconnect();
    }

    protected function priority(string $priority, string $importance): EmailPriorityEnum
    {
        if (empty($priority) && empty($importance)) {
            return EmailPriorityEnum::Normal;
        }

        $p = Str::of($priority);

        if ($p->contains('high', true)) {
            return EmailPriorityEnum::Important;
        }

        if ($p->contains('low', true)) {
            return EmailPriorityEnum::Low;
        }
        $i = Str::of($importance);
        if ($i->contains('high', true)) {
            return EmailPriorityEnum::Important;
        }

        if ($i->contains('low', true)) {
            return EmailPriorityEnum::Low;
        }


        return EmailPriorityEnum::Normal;
    }

    protected function _flipAddresses(array $addresses): ?array
    {
        $addr = collect();
        foreach ($addresses as $email => $name) {
            $name = ($name) ?? explode('@', $email)[0];
            $name = explode('@', $name)[0];
            $addr->add([$name => $email]);
        }
        if ($addr->isEmpty()) {
            return null;
        }
        return $addr->toArray();
    }

    protected function _parserHtml(string $body): string
    {
        //for outlook specific
        preg_match("/<body[^>]*>(.*?)<\/body>/is", $body, $matches);

        return count($matches) ? $matches[0] : $body;
    }
}
