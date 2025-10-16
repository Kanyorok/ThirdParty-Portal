<?php

namespace App\Services;

use App\Enums\Core\IntegrationsEnum;
use App\Enums\EmailEncryptionEnum;
use App\Enums\EmailPriorityEnum;
use App\Enums\EmailStatusEnum;
use App\Enums\EmailTypeEnum;
use App\Events\EmailSendEvent;
use App\Exceptions\ErroredException;
use App\Helpers\StringHelper;
use App\Helpers\SystemHelper;
use App\Mail\DefaultEmail;
use App\Mail\TestMail;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\Communication\Email;
use App\Models\Communication\EmailConversation;
use App\Models\CRM\Campaign;
use App\Models\CRM\CampaignParty;
use App\Models\Fleet\FleetDriver;
use App\Models\CRM\Contact;
use App\Models\CRM\Lead;
use App\Models\DMS\Image;
use App\Models\Settings\APICredential;
use App\Models\ThirdParies\Board;
use App\Services\DMS\ImageService;
use App\Services\Marketing\CampaignService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;
use SensitiveParameter;
use Yajra\DataTables\DataTables;

class CRMEmailService
{
    public function __construct(public Email $crmEmail)
    {
    }

    public static function createClient(Client $client, string $to, string $subject, string $body, User $actor, array $cc = [], EmailPriorityEnum $priorityEnum = null, Email $replyTo = null): CRMEmailService
    {
        return self::create($actor, $subject, $body, ($priorityEnum) ?? EmailPriorityEnum::Normal, [[$client->Name => $to]], Client::getPrimaryKey(), $client->ClientID, $cc, replyTo: $replyTo);
    }

    private static function create(User $actor, string $subject, string $body, EmailPriorityEnum $priority, array $to, string $Party, string $PartyID, array $cc = [], array $bcc = [], Email $replyTo = null): CRMEmailService
    {
        if (!$replyTo instanceof Email && !Str::contains($subject, ['RE:', config('org.name')])) {
            $subject .= ' - ' . config('org.name');
        }
        $crmEmail = new Email();
        $crmEmail->fill([
            'Type' => EmailTypeEnum::Outgoing->value,
            'Status' => EmailStatusEnum::Draft->value,
            'Priority' => $priority->value,
            'From' => 'temp@craftsilicon.com',
            'To' => $to,
            'CC' => $cc,
            'BCC' => $bcc,
            'Subject' => $subject,
            'Body' => $body,
            'Text' => StringHelper::cleanHtml($body),
            'Party' => $Party,
            'PartyID' => $PartyID,
            'EmailConversationId' => $replyTo?->EmailConversationId,
            'ReferenceId' => $replyTo?->MailID,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ])->save();

        return new self($crmEmail);
    }

    public static function createContact(Contact $contact, string $to, string $subject, string $body, User $actor, array $cc = [], EmailPriorityEnum $priorityEnum = null, Email $replyTo = null): CRMEmailService
    {
        return self::create($actor, $subject, $body, ($priorityEnum) ?? EmailPriorityEnum::Normal, [[$contact->Name => $to]], Contact::getPrimaryKey(), $contact->ContactID, $cc, replyTo: $replyTo);
    }

    public static function createTeam(Team $team, string $subject, string $body, User $actor, array $cc = []): CRMEmailService
    {
        return self::create($actor, $subject, $body, EmailPriorityEnum::Normal, [[$team->Name => $team->Email]], Team::getPrimaryKey(), $team->TeamID, $cc);
    }

    public static function createLead(Lead $lead, string $to, string $subject, string $body, User $actor, array $cc = [], Email $replyTo = null): CRMEmailService
    {
        return self::create($actor, $subject, $body, EmailPriorityEnum::Normal, [[$lead->Name => $to]], Lead::getPrimaryKey(), $lead->LeadID, $cc, replyTo: $replyTo);
    }

    public static function createUser(User $user, string $subject, string $body, User $actor, array $cc = [], EmailPriorityEnum $priorityEnum = EmailPriorityEnum::Normal, Email $replyTo = null): CRMEmailService
    {
        return self::create($actor, $subject, $body, $priorityEnum, [[$user->Name => $user->Email]], User::getPrimaryKey(), $user->Id, $cc, replyTo: $replyTo);
    }

    public static function createDriver(FleetDriver $driver, string $subject, string $body, User $actor, array $cc = [], EmailPriorityEnum $priorityEnum = EmailPriorityEnum::Normal, Email $replyTo = null): CRMEmailService
    {
        return self::create($actor, $subject, $body, $priorityEnum, [[$driver->FullName => $driver->Email]], FleetDriver::getPrimaryKey(), $driver->Id, $cc, replyTo: $replyTo);
    }



    public static function createBoard(Board $board, string $subject, string $body, User $actor, array $cc = [], EmailPriorityEnum $priorityEnum = EmailPriorityEnum::Normal): CRMEmailService
    {
        return self::create($actor, $subject, $body, $priorityEnum, [[$board->Name => $board->Email]], Board::getPrimaryKey(), $board->Id, $cc);
    }

    /**
     * @throws Exception
     */
    public static function dt(Builder|MorphMany $query, array $with = []): JsonResponse
    {
        $query->lock('WITH(NOLOCK)');
        if (!empty($with)) {
            $query->with($with);
        }

        return Datatables::of($query->select('*'))
            ->addIndexColumn()
            ->addColumn('id', fn(Email $email) => $email->EmailID) // 👈 required for JS
            ->addColumn('action', function (Email $email) {
                return '<button class="btn btn-sm btn-primary view-email" data-id="' . $email->EmailID . '">
                        <i class="fas fa-eye"></i> View
                    </button>';
            })
            ->editColumn('Type', fn(Email $email) => $email->Type->name)
            ->editColumn('CreatedOn', fn(Email $email) => $email->CreatedOn?->format('F d, Y h:i A'))
            ->editColumn('Dated', function (Email $email) {
                return $email->Dated instanceof Carbon
                    ? $email->Dated->format('F d, Y h:i A')
                    : $email->CreatedOn?->format('F d, Y h:i A');
            })
            ->rawColumns(['action'])
            ->make();
    }

    public static function testConfig(string $host, int $port, EmailEncryptionEnum $encryption, string $username, #[SensitiveParameter] string $password): bool
    {
        try {
            $mailer = clone app('mailer');
            $mailer->alwaysFrom($username, config('org.name'));
            $mailer->alwaysTo($username, config('org.name'));
            $mailer->setSymfonyTransport(Mail::createSymfonyTransport([
                'transport'  => 'smtp',
                'timeout'    => 5,
                'host'       => $host,
                'port'       => $port,
                'encryption' => $encryption->value, // Ensure it's a string (e.g., 'tls', 'ssl')
                'username'   => $username,
                'password'   => $password,
            ]));

            return $mailer->sendNow(new TestMail()) instanceof SentMessage;

        } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
            Log::error('Mail transport error', [
                'host' => $host,
                'port' => $port,
                'encryption' => $encryption->value,
                'username' => $username,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        } catch (\Exception $e) {
            Log::error('General mail config error', [
                'host' => $host,
                'port' => $port,
                'encryption' => $encryption->value,
                'username' => $username,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return false;
    }


    public function getParty(): string
    {
        return ($this->crmEmail->Type->value === EmailTypeEnum::Incoming->value)
            ? $this->crmEmail->From
            : $this->getTo(true);
    }

    public function getTo(bool $one = false): string
    {
        $emails = '';
        foreach (collect($this->crmEmail->To)->flatten()->toArray() as $email => $name) {
            $emails .= $name . ' <' . $email . '>, ';
            if ($one) {
                if (is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return $email;
                }
                if (is_string($name) && filter_var($name, FILTER_VALIDATE_EMAIL)) {
                    return $name;
                }
            }
        }
        return $emails;
    }

    public function getCc(bool $one = false): string
    {
        $emails = '';
        foreach ($this->crmEmail->CC as $email => $name) {
            $emails .= $name . ' <' . $email . '>, ';
            if ($one) {
                return $email;
            }
        }
        return $emails;
    }

    public function createReply(string $To, string $subject, string $body, User $actor, array $cc = []): CRMEmailService
    {
        return self::create($actor, $subject, $body, $this->crmEmail->Priority, [[$To => $To]], ($this->crmEmail->Party) ?? '', ($this->crmEmail->PartyID) ?? "", $cc, /*$this->crmEmail->BCC*/ [], $this->crmEmail);
    }

    public function addAttachmentUpload(UploadedFile $file, User $actor): static
    {
        return $this->addAttachmentContent($file->getContent(), ($file->getMimeType()) ?? $file->getClientMimeType(), $file->getFilename(), $actor);
    }

    public function addAttachmentContent(string $Content, string $MimeType, string $Name, User $actor): static
    {
        return $this->addAttachment(ImageService::create(Email::getPrimaryKey(), $this->crmEmail->EmailID, $Content, $MimeType, $Name, $actor)->image);
    }

    public function addAttachment(Image $image): self
    {
        $this->crmEmail->attachments()->attach($image->ImageID, [], false);
        return $this;
    }

    public function setSource(string $Source, string $SourceID): static
    {
        $this->crmEmail->update([
            'Source' => $Source,
            'SourceID' => $SourceID,
        ]);

        return $this;
    }

    public function autoAttachIncoming(Carbon $dated): static
    {
        if ($this->crmEmail->Type->value !== EmailTypeEnum::Incoming->value) {
            return $this;
        }

        //check if it has ref and auto attach to that conversation && party there off.
        if (!is_null($this->crmEmail->ReferenceId)) {
            $related = Email::query()->whereNotNull('EmailConversationId')->where(function (Builder $query) {
                $query->where('MailID', $this->crmEmail->ReferenceId)->orWhere('ReferenceId', $this->crmEmail->ReferenceId);
            })->with('conversation')->first();
            if ($related instanceof Email) {
                //check if there is a conversation with this email if non create first.
                $conversation = ($related->conversation instanceof EmailConversation)
                    ? $related->conversation
                    : (new self($related))->_createConversation();

                $this->attachToConversation($conversation, true);

                return $this;
            }
        }

        //if no conversation and search party and create a conversation.

        //search client
        $client = Client::query()->where('Email', $this->crmEmail->From)->first(['ClientID', 'Name', 'Email']);
        if ($client instanceof Client) {
            $this->attachClient($client)->addActivity($dated);
            $this->_createConversation();
            return $this;
        }

        //search lead
        $lead = Lead::query()->where('Email', $this->crmEmail->From)->first(['LeadID', 'Name', 'Email']);
        if ($lead instanceof Lead) {
            $this->attachLead($lead)->addActivity($dated);
            $this->_createConversation();
            return $this;
        }

        //search contact
        $contact = Contact::query()->where('Email', $this->crmEmail->From)->with('party')->first(['ContactID', "Party", "PartyID", 'Email']);
        if ($contact instanceof Contact) {
            if ($contact->party instanceof Lead) {
                $this->attachLead($contact->party)->addActivity($dated);
                $this->_createConversation();
                return $this;
            }

            if ($contact->party instanceof Client) {
                $this->attachClient($contact->party)->addActivity($dated);
                $this->_createConversation();
                return $this;
            }
        }

        $this->_createConversation();
        return $this;
    }

    private function _createConversation(): EmailConversation
    {
        $conversation = new EmailConversation();
        $conversation->fill([
            'EmailId' => $this->crmEmail->EmailID,
            'Emails' => 0,
            'Party' => $this->crmEmail->Party,
            'PartyID' => $this->crmEmail->PartyID,
            'CreatedBy' => $this->crmEmail->CreatedBy,
            'ModifiedBy' => $this->crmEmail->CreatedBy,
        ])->save();

        return $this->attachToConversation($conversation);
    }

    protected function attachToConversation(EmailConversation $conversation, bool $changeEmailParty = false): EmailConversation
    {
        $this->crmEmail->update([
            'EmailConversationId' => $conversation->Id,
        ]);
        $conversation->increment('Emails');

        $conversation->update([
            'EmailId' => $this->crmEmail->EmailID,
        ]);

        if ($changeEmailParty) {
            $this->crmEmail->update([
                'PartyID' => $conversation->PartyID,
                'Party' => $conversation->Party,
            ]);
        }
        return $conversation;
    }

    public function addActivity(Carbon $dated, string $description = null): array
    {
        return ActivityService::email($this->crmEmail, $dated, $description);
    }

    public function attachClient(Client $client): static
    {
        $this->crmEmail->update([
            'Party' => Client::getPrimaryKey(),
            'PartyID' => $client->ClientID,
        ]);

        return $this;
    }

    public function attachLead(Lead $lead): static
    {
        $this->crmEmail->update([
            'Party' => Lead::getPrimaryKey(),
            'PartyID' => $lead->LeadID,
        ]);

        return $this;
    }

    public function send(bool $immediate = false): static
    {
        if ($this->crmEmail->Type->value !== EmailTypeEnum::Outgoing->value) {
            return $this;
        }

        if ($this->crmEmail->Status->value === EmailStatusEnum::Queued->value) {
            /* if (!filter_var($this->crmEmail->to, FILTER_VALIDATE_EMAIL)) {
                 $this->_failed();
             }*/
            return $this->_send();
        }

        if ($this->crmEmail->Status->value === EmailStatusEnum::Draft->value) {
            if (!$immediate && config('queue.default') !== 'sync') {
                $this->crmEmail->update([
                    'Status' => EmailStatusEnum::Sending->value,
                ]);

                event(new EmailSendEvent($this->crmEmail));
                return $this;
            }
            return $this->_send();
        }

        if ($this->crmEmail->Status->value === EmailStatusEnum::Sending->value) {
            if (!$immediate && config('queue.default') !== 'sync') {
                return $this;      //already queued for sending.
            }
            return $this->_send();
        }

        return $this;
    }

    protected function _failed(string $reason): static
    {
        $source = $this->crmEmail->source;
        if ($source instanceof CampaignParty) {//update status
            $source->update([
                'Status' => EmailStatusEnum::Failed->value,
                'Channel' => Email::getPrimaryKey(),
                'ChannelID' => $this->crmEmail->EmailID,
            ]);
            if ($source->campaign instanceof Campaign) {
                (new CampaignService($source->campaign))->complete();
            }
        }

        $this->crmEmail->update([
            'Dated' => now(),
            'Status' => EmailStatusEnum::Failed->value,
        ]);
        Log::error('Email failed to send: ' . $reason);

        return $this;
    }

    protected function _send(): static
    {
        if (config('app.debug')) {
            return $this->_failed('In debug');
        }
        //$mailable = Mail::send(new DefaultEmail($this->crmEmail));
        try {
            $mailable = $this->_sendNewConfig();
        } catch (ErroredException $e) {
            SystemHelper::notifyAdmin('send email ' . $e->getMessage());
            return $this->_failed('No Email Config');
        }

        if ($mailable instanceof SentMessage) {
            $this->crmEmail->update([
                'MailID' => $mailable->getMessageId(),
                'Dated' => now(),
                'Status' => EmailStatusEnum::Sent->value,
            ]);

            if (!is_int($this->crmEmail->EmailConversationId)) {
                $this->_createConversation();//create and set non related (new);
            }
            $source = $this->crmEmail->source;
            if ($source instanceof CampaignParty) {//update status
                $source->update([
                    'Status' => EmailStatusEnum::Sent->value,
                    'Channel' => Email::getPrimaryKey(),
                    'ChannelID' => $this->crmEmail->EmailID,
                ]);
                if ($source->campaign instanceof Campaign) {
                    (new CampaignService($source->campaign))->complete();
                }
            }

            if ($this->crmEmail->party instanceof Lead) {
                $this->crmEmail->party->update([
                    'LastContacted' => now(),
                ]);
            }
            return $this;
        }

        return $this->_failed('Unknown error');
    }

    /**
     * @throws ErroredException
     */
    private function _sendNewConfig(): ?SentMessage
    {
        $credentials = APICredential::query()->where('Integration', IntegrationsEnum::Email->value)->latest('Id')->first();
        if (!$credentials instanceof APICredential) {
            throw new ErroredException('no email mail configuration');
        }
        $emailConfig = $credentials->Configuration;

        $this->crmEmail->update([
            'From' => $emailConfig?->Outgoing?->username,
        ]);
        // $mailer = (new MailManager(clone app('mailer')));

        $mailer = clone app('mailer');
        $mailer->alwaysFrom($emailConfig?->Outgoing?->username, config('org.name'));
        // Override email config
        $mailer->setSymfonyTransport(Mail::createSymfonyTransport([
            'transport' => 'smtp',
            'host' => $emailConfig?->Outgoing?->host,
            'port' => $emailConfig?->Outgoing?->port,
            'encryption' => $emailConfig?->Outgoing?->encryption,
            'username' => $emailConfig?->Outgoing?->username,
            'password' => $emailConfig?->Outgoing?->password,
        ]));

        // Send email
        return $mailer->sendNow(new DefaultEmail($this->crmEmail));
    }
}
