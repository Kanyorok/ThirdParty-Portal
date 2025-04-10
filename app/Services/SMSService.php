<?php

namespace App\Services;

use App\Enums\EmailStatusEnum;
use App\Enums\EmailTypeEnum;
use App\Events\SMSSendEvent;
use App\Helpers\SystemHelper;
use App\Models\Board;
use App\Models\BR\Account;
use App\Models\BR\Client;
use App\Models\BulkNotification;
use App\Models\Campaign;
use App\Models\CampaignParty;
use App\Models\CrmSMS;
use App\Models\Lead;
use App\Models\User;
use App\Services\BR\ClientService;
use App\Services\Marketing\CampaignService;
use App\Services\ThirdParty\CSSMSService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;


class SMSService
{
    public function __construct(public CrmSMS $sms)
    {
    }

    public static function createClient(Client $client, string $content, User $actor, string $phoneNo = null): SMSService
    {
        $phoneNo = ($phoneNo) ?? (new ClientService($client))->phoneNo();
        return self::create($actor, ($phoneNo) ?? '?', str_replace(['#name', '#date', '#org'], [$client->Name, Carbon::now()->format('M d, Y'), config('org.name')], $content), Client::getPrimaryKey(), $client->ClientID);
    }

    public static function createBoard(Board $board, string $content, User $actor): SMSService
    {
        return self::create($actor, ($board->Phone) ?? '?', str_replace(['#name', '#date', '#org'], [$board->Name, Carbon::now()->format('M d, Y'), config('org.name')], $content), Board::getPrimaryKey(), $board->Id);
    }

    private static function create(User $actor, string $phoneNo, string $content, string $Party, string $PartyID): SMSService
    {
        $sms = new CrmSMS();
        $sms->fill([
            'SMSId' => self::_id(),
            'Phone' => $phoneNo,
            'Type' => EmailTypeEnum::Outgoing->value,
            'Status' => EmailStatusEnum::Draft->value,
            'Content' => Str::of($content)->remove(["\r", "\n", "\t", "\0", "\x0B"])->replace("\u{A0}", " ")->toString(),
            'Party' => $Party,
            'PartyID' => $PartyID,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ])->save();

        return new SMSService($sms);
    }

    protected static function _id(): string
    {
        $number = CrmSMS::withTrashed()->count();
        do {
            $number++;
            $slug = Str::slug('M' . Str::padLeft(($number), 4, '0'));
        } while (CrmSMS::where('SMSId', $slug)->withTrashed()->exists());

        return $slug;
    }

    public static function createLead(Lead $lead, string $content, User $actor, string $phoneNo = null): SMSService
    {
        $phoneNo = ($phoneNo) ?? $lead->Phone;
        return self::create($actor, $phoneNo, str_replace(['#name', '#date', '#org'], [$lead->Name, Carbon::now()->format('M d, Y'), config('org.name')], $content), Lead::getPrimaryKey(), $lead->LeadID);
    }

    public static function createUser(User $user, string $content, User $actor, string $phoneNo = null): SMSService
    {
        $phoneNo = ($phoneNo) ?? $user->Phone;
        return self::create($actor, $phoneNo, str_replace(['#name', '#date', '#org'], [$user->Name, Carbon::now()->format('M d, Y'), config('org.name')], $content), User::getPrimaryKey(), $user->Id);
    }

    public function setSource(string $Source, string $SourceID): static
    {
        $this->sms->update([
            'Source' => $Source,
            'SourceID' => $SourceID
        ]);

        return $this;
    }

    public function setBulk(BulkNotification $bulkNotification): static
    {
        $this->sms->update([
            'BulkNotificationId' => $bulkNotification->BulkNotificationID
        ]);

        return $this;
    }

    public function send(bool $immediate = false): static
    {
        if ($this->sms->Type->value !== EmailTypeEnum::Outgoing->value) {
            return $this;
        }

        if (Str::length($this->sms->Phone) < 9) {//todo checkpoint is valid
            $this->_failed();
        }

        if ($this->sms->Status->value === EmailStatusEnum::Queued->value) {
            return $this->_send();
        }

        if ($this->sms->Status->value === EmailStatusEnum::Draft->value) {
            if ($immediate || config('queue.default') !== 'sync') {
                return $this->_send();
            }

            $this->sms->update([
                'Status' => EmailStatusEnum::Sending->value
            ]);

            event(new SMSSendEvent($this->sms));
            return $this;
        }

        if ($this->sms->Status->value === EmailStatusEnum::Sending->value) {
            if (!$immediate && config('queue.default') !== 'sync') {
                return $this;      //already queued for sending.
            }
            return $this->_send();
        }

        return $this;
    }


    protected function _failed(): static
    {
        $this->sms->update([
            'Dated' => now(),
            'Status' => EmailStatusEnum::Failed->value
        ]);

        return $this->_UpdateParent(EmailStatusEnum::Failed);
    }

    protected function _UpdateParent(EmailStatusEnum $status): SMSService
    {
        $source = $this->sms->source;
        if ($source instanceof CampaignParty) {//update status
            $source->update([
                'Status' => $status->value,
                'Channel' => CrmSMS::getPrimaryKey(),
                'ChannelID' => $this->sms->Id,
            ]);
            if ($source->campaign instanceof Campaign) {
                (new CampaignService($source->campaign))->complete();
            }
        } else if ($this->sms->bulk instanceof BulkNotification) {
            $this->updateBulkNotification($this->sms->bulk);
        }

        if ($this->sms->Party === Lead::getPrimaryKey() && $this->sms->party instanceof Lead) {
            $this->sms->party->update([
                'LastContacted' => now()
            ]);
        }
        return $this;
    }

    protected function updateBulkNotification(BulkNotification $notification): static
    {
        if ($notification->Total <= $notification->sms()->count()) {
            $notification->update([
                'CompleteOn' => ($this->sms->Dated) ?? now()
            ]);
        }
        return $this;
    }

    protected function _send(): static
    {
        try {
            $response = !config('app.debug') && (new CSSMSService())->sendMessage($this->sms);
        } catch (Exception $e) {
            SystemHelper::notifyAdmin('send sms ' . $e->getMessage());
            return $this->_failed();
        }

        if (!$response) {
            return $this->_failed();
        }

        $this->sms->update([
            'Dated' => now(),
            'Status' => EmailStatusEnum::Sent->value
        ]);

        return $this->_UpdateParent(EmailStatusEnum::Sent);
    }

    public function addActivity(Carbon $dated, string $description = null): array
    {
        return ActivityService::sms($this->sms, $dated, $description);
    }


    /**
     * @throws Exception
     */
    public static function dt(Builder|MorphMany|HasMany $query, array $with = []): JsonResponse
    {
        $query->lock('WITH(NOLOCK)');
        if (!empty($with)) {
            $query->with($with);
        }
        return Datatables::of($query->select('*'))->addIndexColumn()
            ->addColumn('action', function (CrmSMS $sms) {
                return '...';
            })->editColumn('party', function (CrmSMS $sms) use ($with) {
                if (in_array('party', $with, true)) {
                    return (new PartyService($sms->party))->getDTRow();
                }
                return '';
            })->editColumn('source', function (CrmSMS $sms) use ($with) {
                if (in_array('source', $with, true)) {
                    return (new self($sms))->source();
                }
                return '-';
            })->editColumn('SMSId', function (CrmSMS $sms) {
                return Str::upper($sms->SMSId);
            })->editColumn('Type', function (CrmSMS $sms) {
                return $sms->Type->name;
            })->editColumn('CreatedOn', function (CrmSMS $sms) {
                return $sms->CreatedOn?->format('F d, Y h:i A');
            })->editColumn('Dated', function (CrmSMS $sms) {
                if ($sms->Dated instanceof Carbon) {
                    return $sms->Dated->format('F d, Y h:i A');
                }
                return $sms->CreatedOn?->format('F d, Y h:i A');
            })->setRowClass('mouse_pointer user-select-none dbl-click-summary-data')->setRowData([
                'dbl_click_url' => function (CrmSMS $sms) {
                    return route('sms.summary', [$sms->SMSId]);
                }, 'summary_title' => 'sms details.'
            ])->rawColumns(['action', 'party', 'source'])->make();
    }

    public function source(): string
    {
        $source = $this->sms->source;
        if ($source instanceof CampaignParty) {
            if ($source->campaign instanceof Campaign) {
                return 'Campaign: <a href="' . route('campaigns.show', [$source->campaign->CampaignID]) . '">' . Str::limit($source->campaign->Label) . '<a>';
            }
            return 'Campaign: unknown';
        }

        if ($source instanceof Account) {
            return 'Debt Recovery: Ac ' . $source->AccountID;
        }
        return 'Direct';
    }
}
