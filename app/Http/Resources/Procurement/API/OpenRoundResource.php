<?php

namespace App\Http\Resources\Procurement\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class OpenRoundResource extends JsonResource
{
    public function toArray($request): array
    {
        $startDate = $this->StartDate ? Carbon::parse($this->StartDate) : null;
        $endDate = $this->EndDate ? Carbon::parse($this->EndDate) : null;

        return [
            'id' => $this->RoundID,
            'title' => $this->Title,
            'description' => $this->Description,
            'dates' => [
                'start' => $startDate?->toISOString(),
                'end' => $endDate?->toISOString(),
                'is_open' => $endDate?->isFuture() ?? false,
                'is_closing_soon' => $endDate
                    ? $endDate->isFuture() && $endDate->diffInDays(now()) <= 5
                    : false,
            ],
            'status' => $this->Status,
            'targeted_categories' => $this->whenLoaded(
                'supplierCategories',
                fn () => $this->supplierCategories->map(fn ($cat) => [
                    'id' => (int) $cat->SupplierCategoryID,
                    'name' => $cat->CategoryName,
                    'code' => $cat->Code ?? null,
                ])->values()
            ),

            'limits' => [
                'max_vendors' => $this->MaxVendors,
            ],
            'timestamps' => [
                'created_at' => $this->CreatedOn
                    ? Carbon::parse($this->CreatedOn)->toISOString()
                    : null,
                'modified_at' => $this->ModifiedOn
                ? Carbon::parse($this->ModifiedOn)->toISOString()
                : null,
            ],
        ];
    }
}
