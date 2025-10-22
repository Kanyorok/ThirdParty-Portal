<?php

namespace App\Services\Property\PropertyRegistry;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Core\CategoryMaster;
use App\Models\Core\Country;
use App\Models\Core\Locality;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyType;
use Illuminate\Http\UploadedFile;
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
        string         $PropertyName,
        string         $PropertyCode,
        PropertyType   $PropertyType,
        CategoryMaster $Category,
        string         $Owner,
        Carbon         $AcquisitionDate,
        Country  $CountryId,
        Locality $LocationId,
        string   $Address,
        string         $PropertyDescription = null,
        User   $user,
        UploadedFile $document = null
    ): self
    {
        $property = PropertyRegistry::create([
            'PropertyName' => $PropertyName,
            'PropertyCode' => $PropertyCode,
            'PropertyType' => $PropertyType->Id,
            'Category' => $Category->Id,
            'Owner' => $Owner,
            'AcquisitionDate' => $AcquisitionDate,
            'CountryId' => $CountryId->Id,
            'LocationId' => $LocationId->ID,
            'Address' => $Address,
            'PropertyDescription' => $PropertyDescription ?? '',
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);
        if ($document) {
            $property->newDocument(
                ModulesEnum::Property,
                $document,
                [PermissionEnum::PropertyRegistryView->value],
                $user
            );
        }

        activity()->causedBy(auth()->user()->Id)->performedOn($property)->event('create')->log("Added Property {$property->Id}.");
        return new self($property);
    }

public static function update(
    PropertyRegistry $property,
    string           $PropertyName,
    string           $PropertyCode,
    PropertyType     $PropertyType,
    CategoryMaster   $Category,
    string           $Owner,
    Carbon           $AcquisitionDate,
    Country  $CountryId,
    Locality $LocationId,
    string   $Address,
    ?string  $PropertyDescription = null,
    User             $user,
    bool     $IsActive,
    UploadedFile     $document = null
): self {
    $property->update([
        'PropertyName'        => $PropertyName,
        'PropertyCode'        => $PropertyCode,
        'PropertyType'        => $PropertyType->Id,
        'Category'            => $Category->Id,
        'Owner'               => $Owner,
        'AcquisitionDate'     => $AcquisitionDate,
        'CountryId' => $CountryId->Id,
        'LocationId' => $LocationId->ID,
        'Address' => $Address,
        'PropertyDescription' => $PropertyDescription ?? '',
        'ModifiedBy'          => $user->Id,
        'IsActive' => $IsActive,
        'ModifiedOn'          => now(),
    ]);

    if ($document) {
        $property->newDocument(
            ModulesEnum::Property,
            $document,
            [PermissionEnum::PropertyRegistryView->value],
            $user
        );
    }

    activity()
        ->causedBy($user->Id)
        ->performedOn($property)
        ->event('update')
        ->log("Updated Property {$property->Id}.");

    return new self($property);
}

}
