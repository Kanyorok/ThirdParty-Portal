<?php

namespace App\Http\Resources\Marketing;

use App\Models\MarketingPlannerActivity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlannerActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $s = $this->resource;
        if (!$s instanceof MarketingPlannerActivity) {
            $s = $s->resource;
        }
        return [
                'id'          => $this->PlannerActivityID,
                'title'       => $this->Name,
                'start'       => $this->StartOn->toIso8601String(),
                'end'         => $this->EndOn->toIso8601String(),
                'description' => $this->Notes,
                'actions'     => [
                                  'show' => route('planner-activities.show', [$s->planner->PlannerID, $this->PlannerActivityID]),
                                 ],
               ];
    }
}
