<?php

namespace App\Http\Resources\Feedback;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                'id'          => $this->SurveyID,
                'Label'       => $this->Label,
                'Description' => ($this->Notes) ?? '',
                'Dates'       => [
                                  'Start' => $this->StartOn?->format('d-m-Y H:i'),
                                  'End'   => $this->EndOn?->format('d-m-Y H:i'),
                                 ],
               ];
    }
}
