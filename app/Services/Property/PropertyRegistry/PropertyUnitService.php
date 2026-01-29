<?php

namespace App\Services\Property\PropertyRegistry;

use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyUnit;

class PropertyUnitService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public PropertyUnit $propertyUnit)
    {
    }

    public static function create(
        PropertyRegistry $propertyId,
        PropertyBlock $blockId,
        PropertyFloor $FloorID,
        string $UnitCode,
        int $UnitSize,
        bool $IsRentable,
        bool $CurrentStatus,
        string $Remarks = null,
        User $user
    ): self {
        $propertyUnit = PropertyUnit::create([
            'PropertyID' => $propertyId->Id,
            'BlockID' => $blockId->Id,
            'FloorID' => $FloorID->Id,
            'UnitCode' => $UnitCode,
            'UnitSize' => $UnitSize,
            'IsRentable' => $IsRentable ? 1 : 0,
            'CurrentStatus' => $CurrentStatus ? 1 : 0,
            'Remarks' => $Remarks,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($propertyUnit)->event('create')->log("Added Property Unit {$propertyUnit->Id}.");

        return new self($propertyUnit);
    }
}
