<?php

namespace App\Http\Resources;

use App\Services\BR\ClientService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ClientCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                'data' => $this->collection->transform(function ($client) {
                /* $govtNo =  match ($client->ClientTypeID) {
                     'E', 'I', 'G', 'M' => $client->resource->individual?->PassportNo,
                     'CH', 'C', 'JNT' => $client->resource->corporate?->CertificateNo,
                     default => '?',
                 };*/
                    return  [
                             'id'       => $client->ClientID,
                    //'GovtNo' => ($govtNo)??'?',
                             'name'     => $client->Name,
                             'type'     => $client->type->Description,
                             'contacts' => [
                                            'email'  => $client->Email,
                                            'main'   => (new ClientService($client->resource))->phoneNo(),
                                            'phone1' => ($client->Phone1) ?? '',
                                            'phone2' => ($client->Phone2) ?? '',
                                            'mobile' => ($client->Mobile) ?? '',
                                           ],
                             'url'      => route('clients.show', $client->ClientID),
                            ];
                }),
               ];
    }
}
