<?php

namespace App\Services\Property\PropertyRegistry;

use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyRegistry;

class PropertyBlockService
{
    /**
     * Create a new class instance.
     */
    public function __construct(PropertyBlock $propertyBlock)
    {
    }
    public static function create(
        PropertyRegistry $PropertyID,
        string $BlockName,
        string $Description,
        User $user
    ):self {
        $block = PropertyBlock::create([
            'PropertyID'    => $PropertyID->Id,
            'BlockName' => $BlockName,
            'Description'   => $Description,
            'CreatedBy' => $user->Id,
            'ModifiedBy'    => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($block)->event('create')->log("Added Property Block {$block->Id}.");
        return new self($block);
    }
}
