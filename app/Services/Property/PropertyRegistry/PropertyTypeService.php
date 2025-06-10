<?php

namespace App\Services\Property\PropertyRegistry;

use App\Models\Auth\User;
use App\Models\Core\CategoryMaster;
use App\Models\PropertyManagement\PropertyType;

class PropertyTypeService
{
    /**
     * Create a new class instance.
     */
    public function __construct(PropertyType $propertyType)
    {
    }

    public static function create(
        string         $PropertyTypeName,
        CategoryMaster $PropertyCategoryId,
        string         $Description,
        User           $user
    ): self
    {
        $propetytype = PropertyType::create([
            'PropertyTypeName' => $PropertyTypeName,
            'PropertyCategoryId' => $PropertyCategoryId->Id,
            'Description' => $Description,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($propetytype)->event('create')->log("Added Property type {$propetytype->Id}.");
        return new self($propetytype);
    }

}
