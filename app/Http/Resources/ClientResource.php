<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $govtNo = match ($this->ClientTypeID) {
            'E', 'I', 'G', 'M' => $this->resource->individual?->PassportNo,
            'CH', 'C', 'JNT' => $this->resource->corporate?->CertificateNo,
            default => '?',
        };
        $phones = collect();
        if (! empty($client->Mobile)) {
            $phones->add($client->Mobile);
        }
        if (! empty($client->Phone1)) {
            $phones->add($client->Phone1);
        }
        if (! empty($client->Phone2)) {
            $phones->add($client->Phone2);
        }

        return [
                'id' => $this->ClientID,
                'GovtNo' => $govtNo,
                'Name' => $this->Name,
                'Type' => $this->resource->type->Description,
                'Contacts' => [
                               'Emails' => (is_string($this->Email)) ? [$this->Email] : [],
                               'PhoneNumbers' => $phones,
                              ],
                'Assets' => [
                               'Image' => $this->resource->getImage('class="img-fluid img-thumbnail"'),
                              ],
               ];
    }
}
