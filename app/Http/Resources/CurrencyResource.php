<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->Id,
            'name' => $this->Name,
            'code' => $this->Code,
            'symbol' => $this->Symbol,
            'symbolNative' => $this->SymbolNative,
            'decimalDigits' => $this->DecimalDigits,
            'rounding' => $this->Rounding,
        ];
    }
}
