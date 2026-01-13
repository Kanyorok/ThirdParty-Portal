<?php 

namespace App\Http\Resources\Procurement\API;

use Illuminate\Http\Resources\Json\JsonResource;

class OpenRoundResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->RoundID,
            'title' => $this->Title,
            'description' => $this->Description,
            'dates' => [
                'start' => $this->StartDate,
                'end' => $this->EndDate,
                'is_closing_soon' => now()->diffInDays($this->EndDate) < 5,
            ],
            'status' => $this->Status,
            'targeted_categories' => $this->supplierCategories->map(function ($cat) {
                return [
                    'id' => $cat->SupplierCategoryID,
                    'name' => $cat->Name,
                    'code' => $cat->Code ?? null,
                ];
            }),
            'metadata' => [
                'max_vendors' => $this->MaxVendors,
                'created_at' => $this->CreatedOn,
            ]
        ];
    }
}