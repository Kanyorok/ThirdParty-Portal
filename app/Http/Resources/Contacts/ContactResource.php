<?php

namespace App\Http\Resources\Contacts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                'active' => $this->resource['active'],
                'address' => null,
                'description' => null,
                'email' => null,
                'id' => $this->resource['id'],
                'job_title' => null,
                'language' => 'en',
                'mobile' => null,
                'name' => $this->resource['name'],
                'phone' => $this->resource['phone'],
                'time_zone' => 'nairobi',
                'twitter_id' => null,
                'custom_fields' => [],
                'facebook_id' => null,
               ];
        /*  [
      {
          "active" : bool,
          "address : null,
          "email" : null,
          "description": null,
          "id": ClientID,
          "job_title": null,
          "laguage": "en",
          "mobile": null,
          "name": "",
          "phone": "",
          "time_zone": "Nairobi",
          "twitter_id": null,
          "custom_fields": {},
          "facebook_id": null
      }
  ]*/
    }
}
