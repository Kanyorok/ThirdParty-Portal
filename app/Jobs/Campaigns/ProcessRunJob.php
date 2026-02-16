<?php

namespace App\Jobs\Campaigns;

use App\Enums\CampaignStatusEnum;
use App\Enums\CampaignTypeEnum;
use App\Enums\EmailPriorityEnum;
use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\CRM\Campaign;
use App\Models\CRM\CampaignParty;
use App\Models\CRM\Lead;
use App\Services\ActivityService;
use App\Services\HRM\UserService;
use App\Services\Marketing\CampaignService;
use App\Services\ThirdParty\InfobipService;
use Carbon\Carbon;
use ErrorException;
use Exception;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessRunJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;


    public int $timeout = 43200;

    /**
     * Delete the job if its models no longer exist.
     */
    public bool $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     */
    public function __construct(public Campaign $campaign, protected readonly User $actor)
    {
    }

    public function uniqueId(): string
    {
        return $this->campaign->Id;

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $lock = Cache::lock($this->campaign->CampaignID . '-process-run-job', 60 * 30); // 30 minutes lock
        if (! $lock->get()) {
            return;
        }
        if ($this->campaign->Type->value === CampaignTypeEnum::Email->value) {
            $this->_email($this->campaign, $this->actor);

            return;
        }

        if ($this->campaign->Type->value === CampaignTypeEnum::SMS->value) {
            $this->_sms($this->campaign, $this->actor);

            return;
        }

        $this->campaign->update([
            'Processing' => false,
        ]);

        $this->campaign->contacts()->update([
            'Status' => CampaignStatusEnum::Failed->value,
        ]);

        (new UserService($this->actor))->sendEmail(
            subject: 'Campaign Failed: Invalid Campaign Type',
            body: 'The campaign "' . $this->campaign->Label . '" failed to proceed because of an invalid campaign type. Please check the campaign settings and try again.',
            immediate: true,
            priorityEnum: EmailPriorityEnum::Important
        );
    }

    protected function _email(Campaign $campaign, User $actor): void
    {
        /*try {
            new InfobipService();
        } catch (Exception|ErrorException) {
            $campaign->contacts()->update([
                'Status' => CampaignStatusEnum::Failed->value,
            ]);

            $campaign->update([
                'Status' => CampaignStatusEnum::Failed->value,
                'Processing' => false
            ]);

            (new UserService($actor))->sendEmail(
                subject: 'Campaign Failed: Invalid Infobip Configuration',
                body: 'The campaign "' . $campaign->Label . '" failed to proceed because of an invalid Infobip configuration. Please check the integration settings and try again.',
                immediate: true,
                priorityEnum: EmailPriorityEnum::Important
            );
            Log::error('Campaign ' . $campaign->CampaignID . ' failed to process because of invalid Infobip configuration');
            return;
        }*/

        $description = 'Campaign ' . $campaign->CampaignID . ' sent via Email';

        try {
            DB::transaction(static function () use ($campaign, $actor, $description) {
                /*
                    1. @CampaignID				int,
                    2. @UserID					int,
                    3. @ActivityDesc			varchar(1000),
                    4. @CampaignRef			varchar(255),
                    5. @CampaignSubject		varchar(200),
                    6. @CampaignMessage		varchar(max),
                    7. @CampaignSource			varchar(50)
                */
                DB::statement('EXEC p_ProcessCampaignEmail ?, ?, ?, ?, ?, ?, ?', [$campaign->Id, $actor->Id, $description, $campaign->CampaignID, $campaign->Label, $campaign->Details, $campaign->list->Source ?? 'ClientID']);

                (new CampaignService($campaign))->complete();
            });
            Log::info('Campaign ' . $campaign->CampaignID . ' sent via Email');

            return;
        } catch (Throwable|Exception $e) {
            Log::error('Processing Campaign Contacts Failed for sp : ' . $e);
            (new USerService($actor))->sendEmail('Process Campaign Failed for sp', '<div><p>Hello</p><p>The campaign <a href="' . route('campaigns.show', [$campaign->CampaignID]) . '"><b>' . $campaign->Label . '</b></a> you have approved, has failed to process contact support<p><div>');

            return;
        }

        /* $campaign->contacts()->with('party')->lock('WITH(NOLOCK)')->chunk(165, function ($contacts) use ($service, $description, $date, $campaign, $actor) {
             $data = collect([]);
             $campaign_sending = collect();
             $campaign_failed = collect();
             $leadId_Sent = collect();
             $clientID_sent = collect();
             $LoansActivity = collect();

             foreach ($contacts as $contact) {
                 if (!$contact instanceof CampaignParty) {
                     continue;
                 }

                 $body = str_replace(['#name', '#date', '#org'], [$contact->party->Name, Carbon::now()->format('M d, Y'), config('org.name')], $campaign->Details);

                 if ($contact->party instanceof Lead) {
                     if (!filter_var($contact->party->Email, FILTER_VALIDATE_EMAIL)) {
                         $campaign_failed->add($contact->Id);
                         continue;
                     }
                     $messageID = $service->sendEmail([$contact->party->Email], $campaign->Label, $body, $campaign->CampaignID);

                     $data->add([
                         'To' => [[$contact->party->Name => $contact->party->Email]],
                         'Subject' => $campaign->Label,
                         'Body' => $body,
                         'Text' => StringHelper::cleanHtml($body),
                         'Source' => CampaignParty::getPrimaryKey(),
                         'SourceID' => $contact->Id,
                         'Party' => Lead::getPrimaryKey(),
                         'PartyID' => $contact->party->LeadID,
                         'CreatedBy' => $actor->Id,
                         'ModifiedBy' => $actor->Id,
                         'CreatedOn' => $date,
                         'ModifiedOn' => $date,
                         'Status' => (is_null($messageID)) ? EmailStatusEnum::Failed : EmailStatusEnum::Sent->value,
                         'MailID' => $messageID,
                     ]);

                     if ((is_null($messageID))) {
                         $campaign_failed->add($contact->Id);
                     } else {
                         $leadId_Sent->add($contact->party->LeadID);
                         $campaign_sending->add($contact->Id);
                     }

                     continue;
                 }
                 if ($contact->party instanceof Client) {
                     if (!filter_var($contact->party->Email, FILTER_VALIDATE_EMAIL)) {
                         $campaign_failed->add($contact->Id);
                         continue;
                     }
                     $messageID = $service->sendEmail([$contact->party->Email], $campaign->Label, $body, $campaign->CampaignID);

                     $data->add([
                         'Source' => CampaignParty::getPrimaryKey(),
                         'SourceID' => $contact->Id,
                         'To' => [[$contact->party->Name => $contact->party->Email]],
                         'Subject' => $campaign->Label,
                         'Body' => $body,
                         'Text' => StringHelper::cleanHtml($body),
                         'Party' => Client::getPrimaryKey(),
                         'PartyID' => $contact->party->ClientID,
                         'CreatedBy' => $actor->Id,
                         'ModifiedBy' => $actor->Id,
                         'CreatedOn' => $date,
                         'ModifiedOn' => $date,
                         'Status' => (is_null($messageID)) ? EmailStatusEnum::Failed : EmailStatusEnum::Sent->value,
                         'MailID' => $messageID,
                     ]);
                     //

                     if ((is_null($messageID))) {
                         $campaign_failed->add($contact->Id);
                     } else {
                         $clientID_sent->add($contact->party->ClientID);
                         $campaign_sending->add($contact->Id);
                     }

                     continue;
                 }

                 if ($contact->party instanceof DebtProduct) {
                     if (!$contact->party->client instanceof Client) {
                         $campaign_failed->add($contact->Id);
                         continue;
                     }

                     $email = (new ClientService($contact->party->client))->getEmail();
                     if (is_null($email)) {
                         $campaign_failed->add($contact->Id);
                         continue;
                     }
                     $body = (new LoanService($contact->party))->placeholders($contact->party->client, $body);
                     $messageID = $service->sendEmail([$email], $campaign->Label, $body, $campaign->CampaignID);
                     $data->add([
                         'Source' => CampaignParty::getPrimaryKey(),
                         'SourceID' => $contact->Id,
                         'To' => [[$contact->party->Name => $email]],
                         'Subject' => (new LoanService($contact->party))->placeholders($contact->party->client, $campaign->Label),
                         'Body' => $body,
                         'Text' => StringHelper::cleanHtml($body),
                         'Party' => Client::getPrimaryKey(),
                         'PartyID' => $contact->party->ClientID,
                         'CreatedBy' => $actor->Id,
                         'ModifiedBy' => $actor->Id,
                         'CreatedOn' => $date,
                         'ModifiedOn' => $date,
                         'Status' => (is_null($messageID)) ? EmailStatusEnum::Failed : EmailStatusEnum::Sent->value,
                         'MailID' => $messageID,
                     ]);
                     //

                     $LoansActivity->add([
                         'Party' => Client::getPrimaryKey(),
                         'PartyID' => $contact->party->ClientID,
                         'UserID' => $actor->Id,
                         'Notes' => $description,
                         'ActivityType' => DebtProduct::getPrimaryKey(),
                         'ActivityTypeID' => $contact->party->AccountID,
                         'CreatedBy' => $actor->Id,
                         'ModifiedBy' => $actor->Id,
                         'CreatedOn' => $date,
                         'ModifiedOn' => $date
                     ]);

                     if ((is_null($messageID))) {
                         $campaign_failed->add($contact->Id);
                     } else {
                         $clientID_sent->add($contact->party->ClientID);
                         $campaign_sending->add($contact->Id);
                     }
                     continue;
                 }

                 $campaign_failed->add($contact->Id);
             }
             if ($data->count() > 0) {
                 DB::table('t_CRMEmails')->lock('WITH(NOLOCK)')->insert($data->toArray());
                 CampaignParty::query()->whereIn('t_CampaignParties.Id', $campaign_sending->flatten()->toArray())->update([
                     'Status' => CampaignStatusEnum::Sent->value,
                 ]);
             }
             $this->activitiesAndFailed($campaign_failed, $leadId_Sent, $description, $campaign, $actor, $clientID_sent, $LoansActivity);
         });*/
    }

    protected function activitiesAndFailed(Collection $campaign_failed, Collection $leadId_Sent, string $description, Campaign $campaign, User $actor, Collection $clientID_sent, Collection $LoansActivity): void
    {
        if ($campaign_failed->count() > 0) {
            CampaignParty::query()->lock('WITH(NOLOCK)')->whereIn('t_CampaignParties.Id', $campaign_failed->flatten()->toArray())->update([
                'Status' => CampaignStatusEnum::Failed->value,
            ]);
        }

        if ($LoansActivity->count() > 0) {
            DB::table('t_PartyActivities')->lock('WITH(NOLOCK)')->insert($LoansActivity->toArray());

            return;
        }

        if ($leadId_Sent->count() > 0) {
            $this->_processActivities($leadId_Sent->toArray(), Lead::getPrimaryKey(), $description, $campaign, $actor);
        }
        if ($clientID_sent->count() > 0) {
            $this->_processActivities($clientID_sent->toArray(), Client::getPrimaryKey(), $description, $campaign, $actor);
        }
    }

    protected function _processActivities(string|array $PartyIDs, string $Party, string $description, Campaign $campaign, User $actor): void
    {
        ActivityService::campaignRun($PartyIDs, $Party, $description, $campaign, $actor, Carbon::now());
    }

    protected function _sms(Campaign $campaign, User $actor): void
    {
        $description = 'Campaign ' . $campaign->Label . ' sent via SMS';

        try {
            DB::transaction(static function () use ($campaign, $actor, $description) {
                /*
                1. --@CampaignID=15,
                2. --@UserID=1,
                3. --@ActivityDesc='Test Campaign Sent 15',
                4. --@CampaignRef ='test-sms-sp-client',
                5. --@CampaignMessage = 'hey #name, this is a test message, kindly ignore. TEST15',
                6. --@CampaignSource = 'ClientID'
                */
                DB::statement('EXEC p_ProcessCampaignSMS ?, ?, ?, ?, ?, ?', [$campaign->Id, $actor->Id, $description, $campaign->CampaignID, $campaign->Details, $campaign->list->Source ?? 'ClientID']);

                (new CampaignService($campaign))->complete();
            });

            return;
        } catch (Throwable|Exception $e) {
            Log::error('Processing Campaign Contacts tp SMS Table Failed for sp : ' . $e);
            (new USerService($actor))->sendEmail('Process Campaign Failed for sp', '<div><p>Hello</p><p>The campaign <a href="' . route('campaigns.show', [$campaign->CampaignID]) . '"><b>' . $campaign->Label . '</b></a> you have approved, has failed to process contact support<p><div>');

            return;
        }

        /*$campaign->contacts()->lock('WITH(NOLOCK)')->with('party')->chunk(180, function ($contacts) use ($description, $date, $campaign, $actor) {
            $data = collect([]);
            $campaign_sending = collect();
            $campaign_failed = collect();
            $leadId_Sent = collect();
            $clientID_sent = collect();
            $LoansActivity = collect();


            foreach ($contacts as $contact) {
                if (!$contact instanceof CampaignParty) {
                    continue;
                }

                if ($contact->party instanceof Lead) {
                    $phoneNo = $contact->party->Phone;
                    if (Str::length($phoneNo) < 9) {
                        $campaign_failed->add($contact->Id);
                        continue;
                    }

                    $data->add([
                        'SMSId' => $campaign->CampaignID . '-' . $contact->Id . '-' . Str::random(7),
                        'Phone' => $phoneNo,
                        'Content' => str_replace(['#name', '#date', '#org'], [$contact->party->Name, Carbon::now()->format('M d, Y'), config('org.name')], $campaign->Details),
                        'Source' => CampaignParty::getPrimaryKey(),
                        'SourceID' => $contact->Id,
                        'Party' => Lead::getPrimaryKey(),
                        'PartyID' => $contact->party->LeadID,
                        'CreatedBy' => $actor->Id,
                        'ModifiedBy' => $actor->Id,
                        'CreatedOn' => $date,
                        'ModifiedOn' => $date,
                    ]);
                    $leadId_Sent->add($contact->party->LeadID);
                    $campaign_sending->add($contact->Id);
                    continue;
                }
                if ($contact->party instanceof Client) {
                    $phoneNo = (new ClientService($contact->party))->phoneNo();
                    if (is_null($phoneNo) || Str::length($phoneNo) < 9) {
                        $campaign_failed->add($contact->Id);
                        continue;
                    }

                    $data->add([
                        'SMSId' => $campaign->CampaignID . '-' . $contact->Id . '-' . Str::random(7),
                        'Phone' => $phoneNo,
                        'Content' => Str::of($campaign->Details)->remove(["\r", "\n", "\t", "\0", "\x0B"])
                            ->replace(["\u{A0}", '#name', '#date', '#org'],
                                [" ", $contact->party->Name, Carbon::now()->format('M d, Y'), config('org.name')])->toString(),
                        'Source' => CampaignParty::getPrimaryKey(),
                        'SourceID' => $contact->Id,
                        'Party' => Client::getPrimaryKey(),
                        'PartyID' => $contact->party->ClientID,
                        'CreatedBy' => $actor->Id,
                        'ModifiedBy' => $actor->Id,
                        'CreatedOn' => $date,
                        'ModifiedOn' => $date,
                    ]);
                    $campaign_sending->add($contact->Id);
                    $clientID_sent->add($contact->party->ClientID);

                    continue;
                }
                if ($contact->party instanceof DebtProduct) {
                    if (!$contact->party->client instanceof Client) {
                        $campaign_failed->add($contact->Id);
                        continue;
                    }

                    $phoneNo = (new ClientService($contact->party->client))->phoneNo();
                    if (is_null($phoneNo) || Str::length($phoneNo) < 9) {
                        $campaign_failed->add($contact->Id);
                        continue;
                    }

                    if (!is_string($campaign->Details) || Str::length($campaign->Details) < 3) {
                        $campaign_failed->add($contact->Id);
                        continue;
                    }

                    $data->add([
                        'SMSId' => $campaign->CampaignID . '-' . $contact->Id . '-' . Str::random(7),
                        'Phone' => $phoneNo,
                        'Content' => (new LoanService($contact->party))->placeholders($contact->party->client, $campaign->Details),
                        'Source' => CampaignParty::getPrimaryKey(),
                        'SourceID' => $contact->Id,
                        'Party' => DebtProduct::getPrimaryKey(),
                        'PartyID' => $contact->party->AccountID,
                        'CreatedBy' => $actor->Id,
                        'ModifiedBy' => $actor->Id,
                        'CreatedOn' => $date,
                        'ModifiedOn' => $date,
                    ]);

                    $LoansActivity->add([
                        'Party' => Client::getPrimaryKey(),
                        'PartyID' => $contact->party->ClientID,
                        'UserID' => $actor->Id,
                        'Notes' => $description,
                        'ActivityType' => DebtProduct::getPrimaryKey(),
                        'ActivityTypeID' => $contact->party->AccountID,
                        'CreatedBy' => $actor->Id,
                        'ModifiedBy' => $actor->Id,
                        'CreatedOn' => $date,
                        'ModifiedOn' => $date
                    ]);

                    $campaign_sending->add($contact->Id);
                    $clientID_sent->add($contact->party->ClientID);
                    continue;
                }

                $campaign_failed->add($contact->Id);
            }

            if ($data->count() > 0) {
                DB::table('t_SMS')->lock('WITH(NOLOCK)')->insert($data->toArray());
                CampaignParty::query()->lock('WITH(NOLOCK)')->whereIn('t_CampaignParties.Id', $campaign_sending->flatten()->toArray())->update([
                    'Status' => CampaignStatusEnum::Sending->value,
                    'Channel' => CrmSMS::getPrimaryKey()
                ]);
            }
            $this->activitiesAndFailed($campaign_failed, $leadId_Sent, $description, $campaign, $actor, $clientID_sent, $LoansActivity);
        });

        $campaign->update([
            'Status' => CampaignStatusEnum::Sending->value,
            'Processing' => false
        ]);*/
    }
}
