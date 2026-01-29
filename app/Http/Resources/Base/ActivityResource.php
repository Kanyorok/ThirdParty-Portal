<?php

namespace App\Http\Resources\Base;

use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                'id' => $this->ActivityID,
                'Content' => ActivityService::rendering($this->resource),
                'Notes' => $this->Notes,
                'dated' => [
                              'datetime' => $this->CreatedOn?->format('d-m-Y H:i:s'),
                              'string' => $this->CreatedOn?->diffForHumans(),
                             ],
               ];
    }
}
