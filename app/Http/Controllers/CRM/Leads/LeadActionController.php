<?php

namespace App\Http\Controllers\CRM\Leads;

use App\Enums\LeadStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\LeadService;
use App\Services\StaticListsService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LeadActionController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * @throws ValidationException
     */
    public function onBoarding(/*OnBoardingRequest*/ Request $request, Lead $lead): JsonResponse
    {
        $actor = $request->user();
        $this->authorize('update', $lead);
        /*if (!is_string($actor->ClientID) || !Client::query()->where('ClientID', $actor->ClientID)->exists()) {
            return $this->errored('Kindly add ClientID in your profile.');
        }

        $branch = $request->getBranch();
        $dob = $request->getDob();
        $memberClass = $request->getMemberClass();
        $countryCode = $request->getCountyCode();
        $phone = $lead->Phone;
        if (!Str::startsWith($phone, '+')) {
            if ($countryCode === 'KE' && Str::length($phone) > 9) {
                $phone = (int)$phone;
                $phone = '+254' . $phone;
            } else {
                return $this->errored('Lead phone number may be invalid, format +254701YYYXXX');
            }
        }

        $response = (new CBSService())->createClient($branch, $lead->Gender, $actor, $dob, $lead->Name, $lead->NameOtherNames ?? "",
            $memberClass, $request->validated('GovernmentID'), $request->validated('TaxNo'), $phone, $lead->Email,
            $request->validated('Address1'), $request->validated('Address2'), $countryCode, $lead->JobTitle ?? ''
        );

        if (!is_object($response)) {
            return $this->errored('Could not communicate with core banking, check configuration.');
        }

        $lead->forceFill([
            'ApplicationID' => $response->ClientID,
            'Status' => LeadStatusEnum::Won->value,
            'DeletedOn' => now(),
            'DeletedBy' => $request->user()->Id,
        ])->save();

        activity()->causedBy($actor)->performedOn($lead)->event('win')->log('Marked lead  (L' . $lead->LeadID . ')  as won.');

        try {
            DB::transaction(static function () use ($response, $lead) {
                //change all configurations
                $lead->calls()->limit(300)->update([
                    "Party" => Client::getPrimaryKey(),
                    "PartyID" => $response->ClientID,
                ]);
                $lead->crmmails()->limit(300)->update([
                    "Party" => Client::getPrimaryKey(),
                    "PartyID" => $response->ClientID,
                ]);
                $lead->crmsms()->limit(300)->update([
                    "Party" => Client::getPrimaryKey(),
                    "PartyID" => $response->ClientID,
                ]);
                $lead->activities()->limit(300)->update([
                    "Party" => Client::getPrimaryKey(),
                    "PartyID" => $response->ClientID,
                ]);
                $lead->tickets()->limit(300)->update([
                    "Party" => Client::getPrimaryKey(),
                    "PartyID" => $response->ClientID,
                ]);
                $lead->tasks()->limit(300)->update([
                    "Party" => Client::getPrimaryKey(),
                    "PartyID" => $response->ClientID,
                ]);
                $lead->discussions()->limit(300)->update([
                    "Party" => Client::getPrimaryKey(),
                    "PartyID" => $response->ClientID,
                ]);
            });
        } catch (Exception $e) {
            Log::error('Could not migrate contacts details lead (' . $lead->LeadID . ') to client (' . $response->ClientID . ')');
            Log::error($e);
        }*/
        try {
            DB::transaction(static function () use ($lead, $actor) {
                (new LeadService($lead))->won($actor);
            });
        } catch (\Throwable | Exception $e) {
            Log::error('Lead (' . $lead->LeadID . ') mark as won.');
            Log::error($e);
        }

        return $this->succeeded('lead marked as won, checking cbs when done.', route('leads.index'));
    }

    public function updateStatus(Request $request, Lead $lead): JsonResponse
    {
        $this->authorize('update', $lead);

        $request->validate([
                            'Status'     => [
                                             'required',
                                             'string',
                                             Rule::in(LeadStatusEnum::values()),
                                            ],
                            'LossReason' => [
                                             'required_if:Status,' . LeadStatusEnum::Cold->value,
                                             Rule::exists('t_CRMCodeDetails', 'ID')->where(function (Builder $query) {
                                                                            return $query->where('CodeID', StaticListsService::LeadLossReason);
                                             }),
                                            ],
                           ]);
        if ($request->get('Status') === LeadStatusEnum::Cold->value) {
            $lead->forceFill([
                              'Status'         => LeadStatusEnum::Cold->value,
                              'LeadLossReason' => $request->get('LossReason'),
                              'DeletedOn'      => now(),
                              'DeletedBy'      => $request->user()->Id,
                             ])->save();

            return $this->succeeded('closed successfully', route('leads.index'));
        }

        if ($request->get('Status') === LeadStatusEnum::Won->value) {
            return $this->errored('You cannot on board this lead');
        }

        $lead->update([
                       'Status'     => $request->get('Status'),
                       'ModifiedBy' => $request->user()->Id,
                      ]);

        return $this->succeeded('updated successfully', route('leads.show', $lead->LeadID));
    }

    /**
     * @return JsonResponse
     */
    public function analytics(): JsonResponse
    {
        $this->authorize('viewAny', Lead::class);
        $data = [
                 'hot'    => 0,
                 'warm'   => 0,
                 'recent' => 0,
                 'won'    => 0,
                ];

        $hot = Lead::query()->lock('WITH(NOLOCK)')->where('Status', LeadStatusEnum::Hot->value)->count();
        $warm = Lead::query()->lock('WITH(NOLOCK)')->where('Status', LeadStatusEnum::Warm->value)->count();
        $recent = Lead::query()->lock('WITH(NOLOCK)')->where('CreatedOn', '>', Carbon::now()->startOfDay()->subDays(21))->count();
        $won = Lead::withTrashed()->lock('WITH(NOLOCK)')->where('Status', LeadStatusEnum::Won->value)->whereNull(['ArchivedOn', 'ArchivedBy'])->count();
        data_set($data, 'hot', Number::abbreviate($hot, ($hot > 999) ? 1 : 0));
        data_set($data, 'warm', Number::abbreviate($warm, ($warm > 999) ? 1 : 0));
        data_set($data, 'recent', Number::abbreviate($recent, ($recent > 999) ? 1 : 0));
        data_set($data, 'won', Number::abbreviate($won, ($won > 999) ? 1 : 0));

        return $this->succeeded('ok', data: $data);
    }
}
