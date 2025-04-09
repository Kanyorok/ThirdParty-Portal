<?php

namespace App\Http\Controllers\API\PBX;

use App\Helpers\SystemHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Call\CallLogRequest;
use App\Models\BR\Client;
use App\Models\Call;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\User;
use App\Services\BR\ClientService;
use App\Services\Call\CallService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CallController extends Controller
{
    /**
     * received call
     * //{"phonenumber":"722726154","AgentFirstName":"Dorine","AgentExtension":"246","ContactName":"RIRET MOSES KIPNGENO (M)","StartTime":"3\/11\/2025 6:36:50\u202fAM","EndTime":"3\/11\/2025 6:37:36\u202fAM","CallType":"Inbound","randomNumber":"[CallStartTimeLocal].ToString()]","_token":"***"}
     */
    public function store(CallLogRequest $request): \Illuminate\Http\JsonResponse
    {
        //Log::warning('3cx log: received call');

        $phone= $request->validated('phonenumber');
        $start = $request->getStart();
        $end = $request->getEnd();
        $query = Call::query();
        $user = User::query()->where('ExtensionNo',$request->validated('AgentExtension'))->first();
        if ($user instanceof User){
            $query->where(function (Builder $query) use ($user){
                $query->where('t_Calls.CreatedBy',$user->Id)->orWhere('t_Calls.UserID',$user->Id)->orWhere('t_Calls.ModifiedBy',$user->Id);
            });
            $actor = $user;
        }else{
          $actor= SystemHelper::user();
        }
        $query->whereBetween('t_Calls.StartOn',[$start->copy()->subHours(3),$start->copy()->addHours()])
        ->where('t_Calls.CallTypeID', $request->getCallType()->value);

        $contact = ClientService::search(Client::query(), $phone)->first();
        if ($contact instanceof Client) {
          $call = $query->where('t_Calls.PartyID',$contact->ClientID)->where('t_Calls.Party',Client::getPrimaryKey())->first();
          $service = ($call instanceof Call)
              ? (new CallService($call))
              : CallService::createClient($contact, $request->getCallStatus(),$request->getCallType(), $start,$actor);

            $service->end($end,$request->getCallStatus(),$actor,true)->setResponse($request->all());

            return $this->succeeded('ok');
        }

        $contact = Lead::query()->where(function (Builder $query) use ($request) {
            $query->where('Phone', $request->get('phone'))->orWhere('Phone', '+' . $request->get('phone'));
        })->first();
        if ($contact instanceof Lead) {
            $call = $query->where('t_Calls.PartyID',$contact->LeadID)->where('t_Calls.Party',Lead::getPrimaryKey())->first();
            $service = ($call instanceof Call)
                ? (new CallService($call))
                : CallService::createLead($contact, $request->getCallStatus(),$request->getCallType(), $start,$actor);

            $service->end($end,$request->getCallStatus(),$actor,true)->setResponse($request->all());

            return $this->succeeded('ok');
        }

        $contact = Contact::query()->where(function (Builder $query) use ($request) {
            $query->where('Phone', $request->get('phone'))->orWhere('Phone', '+' . $request->get('phone'));
        })->first();
        if ($contact instanceof Contact) {

            if ($contact->party instanceof Client) {
                //check calls i the call logs for this client/
                $call = $query->where(function (Builder $query) use ($contact) {
                    $query->where(function (Builder $query) use ($contact) {
                        $query->where('t_Calls.PartyID',$contact->ContactID)->where('t_Calls.Party',Contact::getPrimaryKey());
                    })->orWhere(function (Builder $query) use ($contact) {
                        $query->where('t_Calls.PartyID',$contact->party->ClientID)->where('t_Calls.Party',Client::getPrimaryKey());
                    });
                })->first();

                $service = ($call instanceof Call)
                    ? (new CallService($call))
                    : CallService::createClient($contact, $request->getCallStatus(),$request->getCallType(), $start,$actor);

                $service->end($end,$request->getCallStatus(),$actor,true)->setResponse($request->all());

                return $this->succeeded('ok');
            }

            if ($contact->party instanceof Lead) {
                $call = $query->where(function (Builder $query) use ($contact) {
                    $query->where(function (Builder $query) use ($contact) {
                        $query->where('t_Calls.PartyID',$contact->ContactID)->where('t_Calls.Party',Contact::getPrimaryKey());
                    })->orWhere(function (Builder $query) use ($contact) {
                        $query->where('t_Calls.PartyID',$contact->party->LeadID)->where('t_Calls.Party',Lead::getPrimaryKey());
                    });
                })->first();

                $service = ($call instanceof Call)
                    ? (new CallService($call))
                    : CallService::createLead($contact, $request->getCallStatus(),$request->getCallType(), $start,$actor);

                $service->end($end,$request->getCallStatus(),$actor,true)->setResponse($request->all());

                return $this->succeeded('ok');
            }


            //if($contact->PartyID == 0){//Unattached call
            $call = $query->where('t_Calls.PartyID',$contact->ContactID)->where('t_Calls.Party',Contact::getPrimaryKey())->first();
            $service = ($call instanceof Call)
                ? (new CallService($call))
                : CallService::createContact($contact, $request->getCallStatus(),$request->getCallType(), $start,$actor);

            $service->end($end,$request->getCallStatus(),$actor,true)->setResponse($request->all());
            return $this->succeeded('ok');

        }

        //Create contact and attach contact directly
        $contact = Contact::create([
            'Label'=> "Unattached Caller",
            'Phone' => $phone,
            'Party' => Contact::getPrimaryKey(),
            'PartyID' => 0,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id
        ]);

         CallService::createContact($contact, $request->getCallStatus(),$request->getCallType(), $start,$actor)
            ->end($end,$request->getCallStatus(),$actor,true)->setResponse($request->all());

        return $this->succeeded('ok');
    }

    /**
     * missed call
     */
    public function missed(Request $request): \Illuminate\Http\JsonResponse
    {
        Log::warning('3cx log: missed call');
        Log::info(json_encode($request->all()));
        return $this->succeeded('ok');
    }

    /**
     * the agent called customer
     */
    public function outgoing(Request $request): \Illuminate\Http\JsonResponse
    {
        Log::warning('3cx log: agent called customer');
        Log::info(json_encode($request->all()));
        return $this->succeeded('ok');
    }

    /**
     * agent called customer but customer did not pick
     */
    public function noAnswer(Request $request): \Illuminate\Http\JsonResponse
    {
        Log::warning('3cx log: agent called customer but customer did not pick ');
        Log::info(json_encode($request->all()));
        return $this->succeeded('ok');
    }
}
