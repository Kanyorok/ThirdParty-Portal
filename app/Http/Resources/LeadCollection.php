<?php

namespace App\Http\Resources;

use App\Enums\LeadTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LeadCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->transform(function ($lead) {
                $name = explode(' ', $lead->Name);
                $others = ($lead->Type->value === LeadTypeEnum::Individual->value) ? [
                    'FirstName' => $lead->Name,
                    'LastName' => $lead->OtherNames,
                ] : [
                    'name' => $lead->Name,
                ];

                return array_merge($others, [
                    'id' => $lead->LeadID,
                    'type' => $lead->Type->name,
                    'title' => ($lead->JobTitle) ?? '',
                    'contacts' => [
                        'email' => $lead->Email,
                        'phone' => $lead->Phone,
                    ],
                    'relationship' => [
                        'id' => $lead->RelationshipManager->UserID,
                        'name' => $lead->RelationshipManager->Name,
                    ],
                    'url' => route('leads.show', $lead->LeadID),
                ]);
            }),
        ];
    }
}
