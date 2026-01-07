<?php

namespace App\Listeners\Marketing;

use App\Enums\CampaignStatusEnum;
use App\Enums\CampaignTypeEnum;
use App\Enums\EmailStatusEnum;
use App\Events\Marketing\CampaignRunEvent;
use App\Helpers\StringHelper;
use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\BR\DebtProduct;
use App\Models\Communication\SMS;
use App\Models\CRM\Campaign;
use App\Models\CRM\CampaignParty;
use App\Models\CRM\Lead;
use App\Services\ActivityService;
use App\Services\BR\ClientService;
use App\Services\BR\LoanService;
use App\Services\CRMEmailService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CampaignRunListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CampaignRunEvent $event): void
    {

        if ($event->campaign->Type->value === CampaignTypeEnum::Email->value) {
            $this->_email($event->campaign, $event->actor);
            return;
        }

        if ($event->campaign->Type->value === CampaignTypeEnum::SMS->value) {
            $this->_sms($event->campaign, $event->actor);
            return;
        }

        $event->campaign->update(['Processing' => false]);

        $event->campaign->contacts()->update([
            'Status' => CampaignStatusEnum::Failed->value,
        ]);
    }

    protected function _email(Campaign $campaign, User $actor): void
    {



        $date = now();
        $description = 'Campaign ' . $campaign->CampaignID . ' sent via Email';
        $campaign->contacts()->with('party')->lock('WITH(NOLOCK)')->chunk(165, function ($contacts) use ($description, $date, $campaign, $actor) {
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
                    //smtp
                    CRMEmailService::createLead($contact->party, $contact->party->Email, $campaign->Label, $body, $actor)->setSource(Campaign::getPrimaryKey(), $campaign->Id)->send(true);
                    //infobip


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
                        'Status' => EmailStatusEnum::Sending->value,
                    ]);

                    $leadId_Sent->add($contact->party->LeadID);
                    $campaign_sending->add($contact->Id);

                    continue;
                }
                if ($contact->party instanceof Client) {
                    if (!filter_var($contact->party->Email, FILTER_VALIDATE_EMAIL)) {
                        $campaign_failed->add($contact->Id);
                        continue;
                    }
                    //smtp
                    CRMEmailService::createClient($contact->party, $contact->party->Email, $campaign->Label, $body, $actor)->setSource(Campaign::getPrimaryKey(), $campaign->Id)->send(true);
                    //infobip


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
                        'Status' => EmailStatusEnum::Sending->value,
                    ]);

                    //infobip
                    $clientID_sent->add($contact->party->ClientID);
                    $campaign_sending->add($contact->Id);

                    continue;
                }

                if ($contact->party instanceof DebtProduct) {
                    if (!$contact->party->client instanceof Client) {
                        $campaign_failed->add($contact->Id);
                        continue;
                    }

                    $email = (new ClientService($contact->party->client))->getEmail();
                    if (is_null($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $campaign_failed->add($contact->Id);
                        continue;
                    }
                    $body = (new LoanService($contact->party))->placeholders($contact->party->client, $body);
                    //smtp
                    CRMEmailService::createClient($contact->party->client, $email, $campaign->Label, $body, $actor)->setSource(Campaign::getPrimaryKey(), $campaign->Id)->send(true);
                    //infobip

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
                        'Status' => EmailStatusEnum::Sending->value,
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
                        'ModifiedOn' => $date,
                    ]);

                    $clientID_sent->add($contact->party->ClientID);
                    $campaign_sending->add($contact->Id);

                    continue;
                }

                $campaign_failed->add($contact->Id);
            }
            if ($data->count() > 0) {
                DB::table('t_Emails')->lock('WITH(NOLOCK)')->insert($data->toArray());
                CampaignParty::query()->whereIn('t_CampaignParties.Id', $campaign_sending->flatten()->toArray())->update([
                    'Status' => CampaignStatusEnum::Sent->value,
                ]);
            }
            $this->activitiesAndFailed($campaign_failed, $leadId_Sent, $description, $campaign, $actor, $clientID_sent, $LoansActivity);
        });
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


        $date = now();
        $description = 'Campaign ' . $campaign->Label . ' sent via SMS';

        $campaign->contacts()->lock('WITH(NOLOCK)')->with('party')->chunk(180, function ($contacts) use ($description, $date, $campaign, $actor) {
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
                            ->replace(
                                [
                                    "\u{A0}",
                                    '#name',
                                    '#date',
                                    '#org',
                                ],
                                [
                                    " ",
                                    $contact->party->Name,
                                    Carbon::now()->format('M d, Y'),
                                    config('org.name'),
                                ]
                            )->toString(),
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
                        'ModifiedOn' => $date,
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
                    'Channel' => SMS::getPrimaryKey(),
                ]);
            }
            $this->activitiesAndFailed($campaign_failed, $leadId_Sent, $description, $campaign, $actor, $clientID_sent, $LoansActivity);
        });

        $campaign->update([
            'Status' => CampaignStatusEnum::Sending->value,
            'Processing' => false,
        ]);
    }
}
