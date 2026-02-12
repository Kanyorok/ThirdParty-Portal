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
            if (! $email instanceof APICredential) {
                throw new ErroredException('no email mail configuration');
            }
            $emailConfig = $email->Configuration;

            $mailbox = new Mailbox( // 993 crm@imarishasacco.co.ke SSL
                //'{' . config('mail.incoming.host') . ':' . config('mail.incoming.port') . '/' . config('mail.incoming.transport') . '/' . config('mail.incoming.encryption') . '}' . config('mail.incoming.folder'),
                '{' . $emailConfig?->Incoming?->host . ':' . $emailConfig?->Incoming?->port . '/imap/' . $emailConfig?->Incoming?->encryption . '}' . $emailConfig?->Incoming?->folder,
                $emailConfig?->Incoming?->username, // Username for the before configured mailbox
                $emailConfig?->Incoming?->password, // Password for the before configured username
                null,
                "UTF-8" //'UTF-8',
            ); //SE_UID, "US-ASCII")
        } catch (ConnectionException | InvalidParameterException | Exception $e) {
            SystemHelper::notifyAdmin('Issue with fetch mail: ' . $e->getMessage());

            return;
        }



        try {
            $mailIds = $mailbox->searchMailbox('UNSEEN');
        } catch (\Throwable $e) {
            SystemHelper::notifyAdmin('Issue with search mail: ' . $e->getMessage());

            return;
        }

        foreach ($mailIds as $emailID) {
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
                        'MailID' => trim($email->messageId ?? '', '<>'),
                        'Type' => EmailTypeEnum::Incoming->value,
                        'Status' => EmailStatusEnum::Unread->value,
                        'Priority' => $this->priority($email->priority, $email->importance)->value,
                        'From' => $email->fromAddress,
                        'To' => $this->_flipAddresses($email->to),
                        'CC' => $this->_flipAddresses($email->cc),
                        'ReferenceId' => $ref,
                        'Subject' => $this->cleanString($email->subject),
                        'Body' => (empty($email->textHtml)) ? $this->cleanString($email->textPlain) : $this->cleanString($this->processEmailBody($email->textHtml)),
                        'Text' => Str::of($this->cleanString($email->textPlain))->trim()->value(),
                        'CreatedBy' => $actor->Id,
                        'ModifiedBy' => $actor->Id,
                        'Dated' => $dated,
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

    /**
     * Process HTML body for all email types
     */
    protected function processEmailBody(string $html): string
    {
        $client = $this->detectEmailClient($html);

        return match ($client) {
            'outlook' => $this->processOutlookBody($html),
            'gmail' => $this->processGmailBody($html),
            'yahoo' => $this->processYahooBody($html),
            'apple' => $this->processAppleMailBody($html),
            default => $this->processGenericEmailBody($html),
        };
    }

    protected function isGmailEmail(string $html): bool
    {
        $gmailIndicators = [
            // Gmail-specific classes and IDs
            'gmail_',
            'gmail-',
            'gm-',

            // Gmail's default styling
            'font-family:Roboto',
            'font-family:Roboto,Helvetica',
            'font-family:\'Roboto\'',

            // Gmail wrapper divs
            '<div dir="ltr">',
            'id=":',

            // Gmail quoted text
            'class="gmail_quote"',
            'class="gmail_signature"',

            // Gmail's inline image handling
            'data:image/png;base64',
            'cid:',
            'x-msg://',

            // Gmail's mobile/desktop indicators
            'class="mobile-only"',
            'class="desktop-only"',
        ];

        $htmlLower = strtolower($html);

        foreach ($gmailIndicators as $indicator) {
            if (stripos($htmlLower, strtolower($indicator)) !== false) {
                return true;
            }
        }

        // Check for Gmail's quoted-reply pattern
        if (preg_match('/<div[^>]*class="[^"]*gmail_[^"]*"[^>]*>/i', $html)) {
            return true;
        }

        // Check for Gmail's signature pattern
        if (preg_match('/<div[^>]*class="[^"]*signature[^"]*"[^>]*>/i', $html)) {
            return true;
        }

        return false;
    }

    protected function processGmailBody(string $html): string
    {
        // Extract content from body if present
        preg_match("/<body[^>]*>(.*?)<\/body>/is", $html, $matches);

        $content = count($matches) ? $matches[1] : $html;

        // Clean Gmail-specific wrapper divs
        $patterns = [
            // Remove Gmail's quoted text wrapper but keep content
            '/<div[^>]*class="gmail_quote"[^>]*>/i' => '',
            '/<div[^>]*class="[^"]*gmail_signature[^"]*"[^>]*>/i' => '',

            // Clean Gmail's reply separator
            '/<div[^>]*style="[^"]*border-left:[^"]*"[^>]*>/i' => '',

            // Remove Gmail's extra nested divs
            '/<div[^>]*dir="ltr"[^>]*>/i' => '',
            '/<\/div>\s*<\/div>\s*<\/div>/i' => '</div>',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }

        return $this->preserveGmailImages($content);
    }

    protected function preserveGmailImages(string $content): string
    {
        return preg_replace_callback(
            '/<img[^>]+src="(data:image\/[^;]+;base64,[^"]+)"[^>]*>/i',
            static function ($matches) {
                // Clean up base64 data
                $src = $matches[1];
                $src = preg_replace('/\s+/', '', $src);

                return str_replace($matches[1], $src, $matches[0]);
            },
            $content
        );
    }

    protected function isOutlookEmail(string $html): bool
    {
        $outlookIndicators = [
            'mso-',
            'Microsoft',
            'Outlook',
            'Word.Document',
            'WordSection',
            'MsoNormal',
            'Calibri',
            'Cambria',
            '<!--[if gte mso',
            '<!--[if !mso]><!--',
        ];

        foreach ($outlookIndicators as $indicator) {
            if (stripos($html, $indicator) !== false) {
                return true;
            }
        }

        return (preg_match('/<body[^>]*lang=[^>]*>/i', $html));
    }

    /**
     * Process Outlook emails
     */
    protected function processOutlookBody(string $html): string
    {
        preg_match("/<body[^>]*>(.*?)<\/body>/is", $html, $matches);

        if (count($matches)) {
            $content = $matches[1];
        } else {
            $content = $html;
        }

        // Remove any Outlook-specific wrapper divs but keep their content
        $patterns = [
            '/<div[^>]*class="WordSection1"[^>]*>/i',
            '/<\/div>\s*<!-- WordSection1 -->/i',
            '/<div[^>]*class="MsoNormal"[^>]*>/i',
            '/<\/div>\s*<!-- MsoNormal -->/i',
        ];

        return trim(preg_replace($patterns, '', $content));
    }

    protected function processGenericEmailBody(string $html): string
    {
        preg_match("/<body[^>]*>(.*?)<\/body>/is", $html, $matches);

        $content = count($matches) ? $matches[1] : $html;

        return trim(preg_replace('/<\/?body[^>]*>/i', '', $content));
    }

    protected function detectEmailClient($html): string
    {
        $htmlLower = strtolower($html);

        if ($this->isOutlookEmail($html)) {
            return 'outlook';
        }

        if ($this->isGmailEmail($html)) {
            return 'gmail';
        }

        if (
            str_contains($htmlLower, 'yahoo') ||
            str_contains($htmlLower, 'y-mail')
        ) {
            return 'yahoo';
        }

        if (
            str_contains($htmlLower, 'apple-mail') ||
            str_contains($htmlLower, 'iphone')
        ) {
            return 'apple';
        }

        return 'unknown';
    }

    protected function processYahooBody(string $html): string
    {
        preg_match("/<body[^>]*>(.*?)<\/body>/is", $html, $matches);
        $content = count($matches) ? $matches[1] : $html;

        return trim(preg_replace('/<div[^>]*class="[^"]*yahoo-[^"]*"[^>]*>/i', '', $content));
    }

    protected function processAppleMailBody(string $html): string
    {
        preg_match("/<body[^>]*>(.*?)<\/body>/is", $html, $matches);
        $content = count($matches) ? $matches[1] : $html;

        return trim(preg_replace('/style="[^"]*-webkit-[^"]*"/i', '', $content));
    }

    private function cleanString($string): array|string|null
    {
        // 1. Convert to UTF-8 and ignore invalid characters
        //    '//IGNORE' silently discards characters that cannot be represented in the target charset
        return iconv('UTF-8', 'UTF-8//IGNORE', $string);
    }
}
