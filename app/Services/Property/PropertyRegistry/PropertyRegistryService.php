<?php

namespace App\Services\Property\PropertyRegistry;

use App\Models\Core\Locality;
use App\Models\PropertyManagement\PropertyCategory;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyType;
use Illuminate\Support\Carbon;

class PropertyRegistryService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public PropertyRegistry $propertyRegistry)
    {   
    }
    public static function create(
    String $PropertyName,
    String $PropertyCode,
    PropertyType $PropertyType,
    PropertyCategory $Category,
    String $Owner,
    Carbon $AcquisitionDate,
    String $Country,
    Locality $TownCity,
    String $AreaLocality,
    String $PropertyDescription = null,
    ): self
    {
        $property = PropertyRegistry::create([
            'PropertyName' => $PropertyName,
            'PropertyCode' => $PropertyCode,
            'PropertyType' => $PropertyType,
            'Category' => $Category,
            'Owner' => $Owner,
            'AcquisitionDate' => $AcquisitionDate,
            'Country' => $Country,
            'TownCity' => $TownCity,
            'AreaLocality' => $AreaLocality,
            'PropertyDescription' => $PropertyDescription,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);

        activity()->causedBy(auth()->user()->Id)->performedOn($property)->event('create')->log("Added Property {$property->Id}.");
        return new self($property); 
    }

}
