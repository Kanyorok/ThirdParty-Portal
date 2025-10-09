<?php

namespace App\Http\Resources\ThirdParty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\CurrencyResource;

class ThirdPartyBankDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->BankID,
            'thirdPartyId' => $this->ThirdPartyId,
            'bankName' => $this->BankName,
            'branch' => $this->Branch,
            'accountNumber' => $this->AccountNumber,
            'currencyId' => $this->CurrencyId,
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'swiftCode' => $this->SwiftCode,
            'createdOn' => $this->CreatedOn?->toIso8601String(),
            'modifiedOn' => $this->ModifiedOn?->toIso8601String(),
            'createdBy' => $this->CreatedBy,
            'modifiedBy' => $this->ModifiedBy,
        ];
    }
}
