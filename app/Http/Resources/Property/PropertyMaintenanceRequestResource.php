<?php

namespace App\Http\Resources\Property;

use Illuminate\Http\Resources\Json\JsonResource;

class PropertyMaintenanceRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->Id,
            'ticketNumber' => $this->RequestNumber,
            // Use description as title since we don't have a separate title
            'title' => substr($this->IssueDescription, 0, 50) . (strlen($this->IssueDescription) > 50 ? '...' : ''),
            'description' => $this->IssueDescription,
            'status' => 'Open', // Default/Placeholder as Status column is not apparent in fillable
            'priority' => $this->priority?->Code ?? 'Medium',
            'category' => $this->issueType?->Code ?? 'General',
            'propertyId' => $this->Property,
            'unitId' => $this->Unit,
            'propertyName' => $this->property?->PropertyName,
            'unitName' => $this->unit?->UnitNumber,
            'requestedDate' => $this->CreatedOn,
            'reportedBy' => $this->ReportedBy,
        ];
    }
}
