<?php

namespace App\Http\Resources\ThirdParty;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ThirdPartyBankDetailCollection extends ResourceCollection
{
    public $collects = ThirdPartyBankDetailResource::class;

    public function toArray($request): array
    {
        return [
            'data' => $this->collection,
        ];
    }
}
