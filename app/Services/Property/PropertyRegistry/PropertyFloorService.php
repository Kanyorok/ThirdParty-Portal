<?php

namespace App\Services\Property\PropertyRegistry;

use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyRegistry;

class PropertyFloorService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public PropertyFloor $propertyFloor)
    {
    }
    public static function create(
        PropertyRegistry  $propertyId,
        PropertyBlock  $blockId,
        string $floorLabel,
        string $floorNotes,
        User   $user
    ): self {
        $propertyFloor = PropertyFloor::create([
            'PropertyID' => $propertyId,
            'BlockID' => $blockId,
            'FloorLabel' => $floorLabel,
            'FloorNotes' => $floorNotes,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($propertyFloor)->event('create')->log("Added Property Floor {$propertyFloor->Id}.");
        return new self($propertyFloor);
    }

}
