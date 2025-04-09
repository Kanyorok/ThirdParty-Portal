<?php

namespace App\Http\Controllers\Contact;

use App\Enums\LeadStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\AttachContactLeadRequest;
use App\Http\Requests\Contact\AttachContactClientRequest;
use App\Models\BR\Client;
use App\Models\Contact;
use App\Models\CrmSMS;
use App\Models\EmailConversation;
use App\Models\Lead;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactActionsController extends Controller
{
    public function attachLead(AttachContactLeadRequest $request, Contact $contact):JsonResponse
    {
        if ($contact->PartyID !== '0'){
            return $this->errored('contact not found or invalid');
        }
        $lead = $request->getLead();
        if ($lead->Status === LeadStatusEnum::Won->value){
            return $this->errored('lead already won');
        }

        try {
            DB::transaction(static function () use ($lead, $contact, $request) {
                $contact->crmmails()->update([
                    'Party' => Lead::getPrimaryKey(),
                    'PartyID' => $lead->LeadID,
                ]);

                $contact->crmsms()->update([
                    'Party' => Lead::getPrimaryKey(),
                    'PartyID' => $lead->LeadID,
                ]);

                $contact->calls()->update([
                    'Party' => Lead::getPrimaryKey(),
                    'PartyID' => $lead->LeadID,
                ]);

                $contact->update([
                    'Party' => Lead::getPrimaryKey(),
                    'PartyID' => $lead->LeadID,
                ]);

                activity()->causedBy($request->user())->performedOn($contact)->event('create')->log('attached contact to lead : ' . $lead->LeadID);
            });
        } catch (\Throwable|\Exception $e) {
            Log::error('Error attaching   Contact to Lead. e: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('contact attached successfully.', route('leads.show',[$lead->LeadID]));
    }

    public function attachClient(AttachContactClientRequest $request, Contact $contact):JsonResponse
    {
        if ($contact->PartyID !== '0'){
            return $this->errored('contact not found or invalid');
        }
        $client = $request->getClient();

        try {
            DB::transaction(static function () use ($client, $contact, $request) {
                $contact->crmmails()->update([
                    'Party' => Client::getPrimaryKey(),
                    'PartyID' => $client->ClientID,
                ]);

                $contact->crmsms()->update([
                    'Party' => Client::getPrimaryKey(),
                    'PartyID' => $client->ClientID,
                ]);

                $contact->calls()->update([
                    'Party' => Client::getPrimaryKey(),
                    'PartyID' => $client->ClientID,
                ]);

                $contact->update([
                    'Party' => Client::getPrimaryKey(),
                    'PartyID' => $client->ClientID,
                ]);

                activity()->causedBy($request->user())->performedOn($contact)->event('create')->log('attached contact to client : ' . $client->ClientID);
            });
        } catch (\Throwable|\Exception $e) {
            Log::error('Error attaching Contact to Client. e: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('contact attached successfully.', route('clients.show',[$client->ClientID]));

    }
}
