<?php

namespace App\Http\Resources\Property;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PropertyCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'data' => $this->collection->map(function ($property) {
                return [
                    'id'            => $property->Id,
                    'property_name' => $property->PropertyName,
                    'property_code' => $property->PropertyCode,

                    'blocks' => $property->getBlockByProperty->map(function ($block) {
                        return [
                            'id'         => $block->Id,
                            'block_name' => $block->BlockName,

                            'floors' => $block->floor->map(function ($floor) {
                                return [
                                    'id'          => $floor->Id,
                                    'floor_label' => $floor->FloorLabel,
                                    'floor_notes' => $floor->FloorNotes,

                                    'units' => $floor->units
                                        ->where('IsRentable', true)
                                        ->where('CurrentStatus', true)
                                        ->map(function ($unit) {
                                            return [
                                                'id'                 => $unit->Id,
                                                'unit_code'          => $unit->UnitCode,
                                                'unit_size'          => $unit->UnitSize,
                                                'is_rentable'        => (bool) $unit->IsRentable,
                                                'current_status'     => (bool) $unit->CurrentStatus,
                                                'availability_label' => 'Vacant',
                                            ];
                                        })
                                        ->values(),
                                ];
                            })
                            ->filter(fn ($floor) => $floor['units']->isNotEmpty())
                            ->values(),
                        ];
                    })
                    ->filter(fn ($block) => $block['floors']->isNotEmpty())
                    ->values(),
                ];
            }),
        ];
    }
}
