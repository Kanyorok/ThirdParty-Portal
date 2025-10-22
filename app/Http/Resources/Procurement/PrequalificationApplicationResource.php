<?php

namespace App\Http\Resources\Procurement;

use App\Enums\Procurement\PrequalificationApplicationEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrequalificationApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Normalize status into a simple DTO to avoid casting enum objects to string
        $statusEnum = null;
        if ($this->Status instanceof PrequalificationApplicationEnum) {
            $statusEnum = $this->Status;
        } elseif (is_string($this->Status)) {
            $statusEnum = PrequalificationApplicationEnum::tryFrom($this->Status);
        }

        return [
            'ApplicationID' => $this->ApplicationID,
            'applicationId' => $this->ApplicationID,
            'SupplierID' => $this->SupplierID,
            'RoundID' => $this->RoundID,
            'roundId' => $this->RoundID,
            'CategoryID' => $this->CategoryID,
            // Back-compat, expose the raw code in "Status"
            'Status' => $statusEnum?->value ?? (is_string($this->Status) ? $this->Status : null),
            'status' => $this->when(
                $statusEnum || is_string($this->Status),
                function () use ($statusEnum) {
                    if ($statusEnum instanceof PrequalificationApplicationEnum) {
                        return [
                            'value' => $statusEnum->value,
                            'label' => $statusEnum->getLabel(),
                            'color' => $statusEnum->getColor(),
                            'icon' => $statusEnum->getIcon(),
                        ];
                    }

                    // If not an enum instance but a string code, provide minimal fields
                    return [
                        'value' => is_string($this->Status) ? $this->Status : null,
                        'label' => is_string($this->Status) ? $this->Status : null,
                    ];
                }
            ),
            'hasApplied' => true,
            'SubmittedOn' => $this->SubmittedOn?->format('Y-m-d H:i:s'),
            'Evaluations' => PrequalificationEvaluationResource::collection($this->whenLoaded('evaluations')),
        ];
    }
}
