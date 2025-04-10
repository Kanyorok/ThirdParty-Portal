<?php

namespace App\Http\Resources;

use App\Models\Call;
use App\Models\Schedule;
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $s = $this->resource;
        if (!$s instanceof Schedule) {
            $s = $s->resource;
        }
        $scheduleService = new ScheduleService($s);

        return [
            'id' => $this->ScheduleID,
            'title' => $this->Title,
            'type' => $this->ScheduledType,
            'start' => $this->StartOn->toIso8601String(),
            'end' =>  $this->EndOn->toIso8601String(),
            'description' => $this->Notes,
            'icon' => ($this->ScheduledType === Call::getPrimaryKey()) ? '<i class="fas fa-phone-alt"></i>' : '<i class="fas fa-calendar-alt"></i>',
            'color' => $scheduleService->colour(),
            'display' => ($this->trashed()) ? 'background' : 'auto',
            'permission' => [
                'editable' => $scheduleService->editable(),
                'cancelable' => $scheduleService->cancelable(),
                'actionable' => $scheduleService->actionable()
            ],
            /* 'clients' => new ClientCollection($scheduleService->clients()->dd()),
             'users' => new UserCollection($scheduleService->users()->paginate(10)),*/
            /*'clients' => new ClientCollection($this->resource->clients()->limit(20)->lock('WITH(NOLOCK)')->get()),
            'users' => new UserCollection($this->resource->users()->limit(20)->lock('WITH(NOLOCK)')->get())*/
        ];
    }
}
