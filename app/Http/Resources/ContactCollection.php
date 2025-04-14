<?php

namespace App\Http\Resources;

use App\Models\BR\Client;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ContactCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                'data' => $this->collection->transform(function ($contact) {
                    if ($contact->party instanceof Lead) {
                        $party = [
                                  'id'   => $contact->party->LeadID,
                                  'name' => $contact->party->Name,
                                  'url'  => route('leads.show', $contact->party->LeadID),
                                  'type' => 'LeadID',
                                 ];
                    } elseif ($contact->party instanceof Client) {
                        $party = [
                                  'id'   => $contact->party->ClientID,
                                  'name' => $contact->party->Name,
                                  'url'  => route('clients.show', $contact->party->ClientID),
                                  'type' => 'ClientID',
                                 ];
                    } else {
                        $party = [
                                  'id'   => $contact->id,
                                  'name' => $contact->name,
                                  'url'  => route('home'),
                                  'type' => '',
                                 ];
                    }

                    return [
                            'id'       => $contact->ContactID,
                            'name'     => $contact->Label,
                            'contacts' => [
                                           'email' => $contact->Email,
                                           'phone' => $contact->Phone,
                                          ],
                            'party'    => $party,
                           ];
                }),
               ];
    }
}
