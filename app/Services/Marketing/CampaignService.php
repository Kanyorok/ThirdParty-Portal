<?php

namespace App\Services\Marketing;

use App\Enums\CampaignStatusEnum;
use App\Enums\CampaignTypeEnum;
use App\Enums\MarketingListEnum;
use App\Enums\WorkflowStatus;
use App\Events\Marketing\CampaignRunEvent;
use App\Events\Marketing\CampaignSubmittedEvent;
use App\Events\Marketing\NewCampaignEvent;
use App\Exceptions\ErroredException;
use App\Models\BR\Client;
use App\Models\BR\DebtProduct;
use App\Models\Campaign;
use App\Models\Lead;
use App\Models\MarketingList;
use App\Models\User;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CampaignService
{
    public function __construct(public Campaign $campaign)
    {
    }

    public static function create(MarketingList $list, string $label, User $actor, CampaignTypeEnum $type, string $notes = '', bool $autoSend = false): CampaignService
    {
        $campaign = new Campaign();
        $campaign->fill([
            'CampaignID' => self::_ID($label),
            'Label' => $label,
            'Status' => CampaignStatusEnum::Draft->value,
            'Type' => $type,
            'Processing' => true,
            'MarketingListId' => $list->MarketingListID,
            'Notes' => $notes,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ])->save();

        event(new NewCampaignEvent($campaign, $actor, $autoSend));

        return (new CampaignService($campaign));
    }

    private static function _ID(string $label): string
    {
        $id = 0;
        do {
            $slug = Str::slug($label);
            $id++;
            if ($id > 1) {
                $slug .= " -" . $id;
            }
        } while (Campaign::withTrashed()->where('CampaignID', $slug)->exists());
        return $slug;
    }

    public function syncFromList(User $actor): static
    {
        //remove all existing
        $this->campaign->contacts()->lock('WITH(NOLOCK)')->delete();

        //attach
        $dated = now();
        $list = $this->campaign->list;

        if ($list instanceof MarketingList) {
            if ($list->Type->value === MarketingListEnum::Static->value) {
                return $this->_syncFromStatic($list, $dated, $actor);
            }
            if ($list->Type->value === MarketingListEnum::Dynamic->value) {
                return $this->_syncFromDynamic($list, $dated, $actor);
            }
        }

        return $this;
    }


    /**
     * @throws ErroredException
     */
    private function _syncFromDynamic(MarketingList $list, Carbon $dated, User $actor): static
    {
        (new DynamicListService($list))->query()->lock('WITH(NOLOCK)')->chunk(260, function ($parties) use ($dated, $actor) {
            $data = collect();
            foreach ($parties as $party) {//  SQL Server supports a maximum of 2100 parameters
                if ($party instanceof Client) {
                    $data->add([
                        'CampaignId' => $this->campaign->Id,
                        'Party' => Client::getPrimaryKey(),
                        'PartyID' => $party->ClientID,
                        'Status' => CampaignStatusEnum::Draft->value,
                        'CreatedBy' => $actor->Id,
                        'ModifiedBy' => $actor->Id,
                        'CreatedOn' => $dated,
                        'ModifiedOn' => $dated
                    ]);
                    continue;
                }
                if ($party instanceof Lead) {
                    $data->add([
                        'CampaignId' => $this->campaign->Id,
                        'Party' => Lead::getPrimaryKey(),
                        'PartyID' => $party->LeadID,
                        'Status' => CampaignStatusEnum::Draft->value,
                        'CreatedBy' => $actor->Id,
                        'ModifiedBy' => $actor->Id,
                        'CreatedOn' => $dated,
                        'ModifiedOn' => $dated
                    ]);
                }
            }
            if ($data->count() > 0) {
                DB::table('t_CampaignParties')->lock('WITH(NOLOCK)')->insert($data->toArray());
            }
        });
        return $this;
    }

    private function _syncFromStatic(MarketingList $list, Carbon $dated, User $actor): static
    {
        $list->parties()->lock('WITH(NOLOCK)')->chunk(200, function ($parties) use ($dated, $actor) {
            $data = collect();
            foreach ($parties as $party) {
                $data->add([
                    'CampaignId' => $this->campaign->Id,
                    'Party' => $party->Party,
                    'PartyID' => $party->PartyID,
                    'Status' => CampaignStatusEnum::Draft->value,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                    'CreatedOn' => $dated,
                    'ModifiedOn' => $dated
                ]);
            }
            if ($data->count() > 0) {
                DB::table('t_CampaignParties')->lock('WITH(NOLOCK)')->insert($data->toArray());
            }
        });
        return $this;
    }

    public function run(User $actor): static
    {
        $this->campaign->fill([
            'Status' => CampaignStatusEnum::Processing,
            'Processing' => true
        ])->save(['timestamps' => false]);

        event(new CampaignRunEvent($this->campaign, $actor));

        return $this;
    }

    public function complete(): static
    {
        if ($this->campaign->Status->value !== CampaignStatusEnum::Sending->value) {
            return $this;
        }
        //check if all sent
        if ($this->campaign->contacts()->whereIn('t_CampaignParties.Status', [CampaignStatusEnum::Draft->value, CampaignStatusEnum::Sending->value,])->exists()) {
            return $this;
        }

        $this->campaign->fill([
            'Status' => CampaignStatusEnum::Sent,
        ])->save(['timestamps' => false]);

        //update marketing list contacted date/
        $this->campaign->list->update([
            'LastContacted' => now(),
        ]);

        return $this;
    }

    /*
        public function sendPartyEmail(CampaignParty $contact, Lead|Client $party, User $actor, bool $immediate = false): void
        {
            $email = $party->Email;
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $contact->update([
                    'Status' => CampaignStatusEnum::Failed->value,
                ]);

                return;
            }
            $service = ($party instanceof Lead) ?
                CRMEmailService::createLead($contact->party, $email, $this->campaign->Label, str_replace(['#name', '#date', '#org'], [$contact->party->Name, Carbon::now()->format('M d, Y'), config('org.name')], $this->campaign->Details), $actor)
                : CRMEmailService::createClient($contact->party, $email, $this->campaign->Label, str_replace(['#name', '#date', '#org'], [$contact->party->Name, Carbon::now()->format('M d, Y'), config('org.name')], $this->campaign->Details), $actor);

            $service->setSource(CampaignParty::getPrimaryKey(), $contact->CampaignId)->send($immediate)->addActivity(now());

            $contact->update([
                'Status' => CampaignStatusEnum::Sending->value,
            ]);
        }

        public function sendPartySMS(CampaignParty $contact, Lead|Client $party, User $actor, bool $immediate = false): void
        {
            $service = ($party instanceof Lead)
                ? SMSService::createLead($contact->party, $this->campaign->Details, $actor)
                : SMSService::createClient($contact->party, $this->campaign->Details, $actor);

            $service->setSource(CampaignParty::getPrimaryKey(), $contact->CampaignId)->send($immediate)->addActivity(now());

            $contact->update([
                'Status' => CampaignStatusEnum::Sending->value,
            ]);
        }*/

    public function workflowApprove(User $actor, bool $notify = true): static
    {
        $this->campaign->pendingWorkflows()->where('Stage', CampaignStatusEnum::Approval)->update([
            'DeletedOn' => now(),
            'DeletedBy' => $actor->Id
        ]);

        $this->campaign->workflows()->create([
            'Stage' => CampaignStatusEnum::Approval->name,
            'Status' => WorkflowStatus::Accepted->value,
            'Notes' => 'Campaign Approval',
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        if ($notify) {
            $owner = $this->campaign->modified;
            if ($owner instanceof User) {
                (new UserService($owner))->sendEmail('Update on Campaign Submission',
                    '<p>Hello</p><p>The campaign <b>' . $this->campaign->Label . '</b>  has been approved. Click the link below to view</p>
                <p><a href="' . route('campaigns.show', [$this->campaign->CampaignID]) . '"> campaign ' . $this->campaign->CampaignID . ' details</a></p>
                <p>This campaign is now active.</p>');
            }
        }
        activity()->causedBy($actor)->performedOn($this->campaign)->event('approve')->log('Approved campaign ' . $this->campaign->CampaignID);

        return $this;
    }

    public function submit(User $actor): static
    {
        if ($this->campaign->list->Source === DebtProduct::getPrimaryKey()) {
            return $this->run($actor);
        }

        $this->campaign->forceFill([
            'Status' => CampaignStatusEnum::Approval->value,
        ])->save(['timestamps' => false]);

        //add workflow
        $this->campaign->workflows()->create([
            'Stage' => CampaignStatusEnum::Draft->name,
            'Status' => WorkflowStatus::Submitted->value,
            'Notes' => 'User Submitted',
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);


        event(new CampaignSubmittedEvent($this->campaign, $actor));

        activity()->causedBy($actor)->performedOn($this->campaign)->event('submit')->log('Submitted ' . $this->campaign->CampaignID . ' for approval.');

        return $this;
    }

    public function workflowReject(User $actor, string $reason): static
    {
        $this->campaign->forceFill([
            'Status' => CampaignStatusEnum::Draft,
        ])->save(['timestamps' => false]);


        $this->campaign->pendingWorkflows()->where('Stage', CampaignStatusEnum::Approval)->update([
            'DeletedOn' => now(),
            'DeletedBy' => $actor->Id
        ]);

        $this->campaign->workflows()->create([
            'Stage' => CampaignStatusEnum::Approval->name,
            'Status' => WorkflowStatus::RejectReturn->value,
            'Notes' => $reason,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);


        $owner = $this->campaign->modified;
        if ($owner instanceof User) {
            (new UserService($owner))->sendEmail('Update on Campaign Submission',
                '<p>Hello</p><p>The campaign <b>' . $this->campaign->Label . '</b>  has been <b style="color: #fa2f43">NOT</b> approved. Click the link below to review</p>
                <p><a href="' . route('campaigns.show', [$this->campaign->CampaignID]) . '"> campaign ' . $this->campaign->CampaignID . ' details</a></p>
                <p><b>Reason Given: </b>&nbsp;' . $reason . '</p>');
        }

        activity()->causedBy($actor)->performedOn($this->campaign)->event('reject')->log('Reject campaign ' . $this->campaign->CampaignID);

        return $this;
    }
}
