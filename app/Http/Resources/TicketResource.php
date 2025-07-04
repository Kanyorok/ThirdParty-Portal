<?php

namespace App\Http\Resources;

use App\Enums\TicketStatusEnum;
use App\Helpers\StringHelper;
use App\Models\BR\Client;
use App\Models\CRM\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $party = [];
        if ($this->party instanceof Lead) {
            $party = [
                'id' => $this->party->LeadID,
                'name' => $this->party->Name,
                'type' => 'lead',
                //'avatar' => $this->party->getImage(' width="32" height="32" class="img-thumbnail" alt="' . $this->party->Name . '"')
            ];
        } elseif ($this->party instanceof Client) {
            $party = [
                'id' => $this->party->ClientID,
                'name' => $this->party->Name,
                'type' => 'client',
                //'avatar' => $this->party->getImage(' width="32" height="32" class="rounded-circle" alt="' . $this->party->Name . '"')
            ];
        }

        return [
            'id' => $this->TicketID,
            'sourceID' => ($this->SourceTicketID) ?? '',
            'title' => $this->Title,
            'description' => Str::limit(StringHelper::cleanHtml($this->Notes), 200),
            'status' => $this->Status->name,
            'priority' => $this->Priority->name,
            'category' => [
                'id' => $this->CategoryID,
                'name' => $this->category->Description,
            ],
            'party' => $party,
            'dated' => [
                'datetime' => $this->CreatedOn?->format('d-m-Y H:i:s'),
                'string' => $this->CreatedOn?->diffForHumans(),
            ],
            'closed' => [
                'datetime' => ($this->Status->value === TicketStatusEnum::Resolved->value) ? $this->ClosedOn?->format('d-m-Y H:i:s') : '',
                'string' => ($this->Status->value === TicketStatusEnum::Resolved->value) ? $this->ClosedOn?->diffForHumans() : '',
                'duration' => ($this->Status->value === TicketStatusEnum::Resolved->value) ? $this->ClosedOn?->diffForHumans($this->CreatedOn, parts: 2, syntax: true) : $this->CreatedOn?->diffForHumans(parts: 2, syntax: true),
            ],
        ];
    }
}
