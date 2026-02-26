<?php

namespace App\Http\Resources\ThirdParty;

use App\Http\Resources\CurrencyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThirdPartyBankDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $extra = (array) ($this->Extra ?? []);

        return [
            'id' => $this->BankID,
            'thirdPartyId' => $this->ThirdPartyId,
            'bankId' => $this->branch?->BankID,
            'bankName' => $this->branch?->bank?->BankName ?? ($extra['bankName'] ?? null),
            'branchId' => $this->BranchID,
            'branch' => $this->branch?->BranchName ?? ($extra['branch'] ?? null),
            'accountNumber' => $this->AccountNumber,
            'currencyId' => $this->CurrencyId,
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'swiftCode' => $this->branch?->bank?->SwiftCode ?? ($extra['swiftCode'] ?? null),
            'createdOn' => $this->CreatedOn?->toIso8601String(),
            'modifiedOn' => $this->ModifiedOn?->toIso8601String(),
            'createdBy' => $this->CreatedBy,
            'modifiedBy' => $this->ModifiedBy,
        ];
    }
}
