<?php

namespace App\Services\Property\PropertyRegistry;

use App\Models\Auth\User;
use App\Models\Core\CategoryMaster;
use phpDocumentor\Reflection\Types\Integer;

class PropertyCategoryService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public CategoryMaster $propertyCategory)
    {   
    }
    public static function create(
    String $propertyCategoryName,
    String $description,
    String $propertytype,
    Int $propertyCode,
    User $user,
    ): self
    {
        $propertycategory = CategoryMaster::create([
            'Name' => $propertyCategoryName,
            'Description'=> $description,
            'Type'=> $propertytype,
            'Code'=> $propertyCode,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy(auth()->user()->Id)->performedOn($propertycategory)->event('create')->log("Added Property category {$propertycategory->Id}.");
        return new self($propertycategory); 
    }
}
