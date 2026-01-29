<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ScheduleCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                'data' => $this->collection->transform(function ($schedule) {
                    return new ScheduleResource($schedule);
                    /*$scheduleService = new ScheduleService($schedule->resource);
                    return  [
                        'id' => $schedule->ScheduleID,
                        'title' => $schedule->Title,
                        'icon' =>  ($schedule->ScheduledType===Call::getPrimaryKey())?'<i class="fas fa-phone-alt"></i>':'<i class="fas fa-calendar-alt"></i>',
                        'start' => $schedule->StartOn->toIso8601String(),
                        'end' =>  $schedule->EndOn->toIso8601String(),
                        'description' => $schedule->Notes,
                        'display' => ($schedule->trashed())?'background':'auto',
                        'color' => $scheduleService->colour(),
                        'permission' => [
                            'editable' => $scheduleService->editable(),
                            'cancelable' => $scheduleService->cancelable(),
                            'actionable' => $scheduleService->actionable()
                        ],
                    ];*/
                }),
               ];
    }
}
