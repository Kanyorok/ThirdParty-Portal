<?php

namespace App\Http\Resources\Procurement\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'ApplicationID' => $this->ApplicationID,
            'SupplierID' => $this->SupplierID,
            'RoundID' => $this->RoundID,
            'CategoryID' => $this->CategoryID,
            'Status' => $this->Status,
            'CategoryName' => $this->category?->CategoryName,
            'Documents' => $this->documents->map(fn ($doc) => [
                'Id' => $doc->Id,
                'Description' => $doc->Description,
                'FileName' => $doc->dmsDocument?->FileName,
                'Url' => asset('storage/' . $doc->dmsDocument?->FilePath),
            ]),
        ];
    }
}
