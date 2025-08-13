<?php

namespace App\Services\Insurance\ProviderAndProducts;

use App\Models\Auth\User;
use App\Models\Insurance\InsuranceProduct;
use App\Models\Insurance\InsuranceProvider;

class InsuranceProductService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public InsuranceProduct $product)
    {
    }
    public static function create(
        InsuranceProvider $InsuranceProviderID,
        string $Name,
        string $Type,
        string $Description,       
        bool $IsActive,
        User   $user
    ) : self {
         
        $product = InsuranceProduct::create([
        'InsuranceProviderID' => $InsuranceProviderID->Id,
        'Name' => $Name,
        'Type' => $Type,
        'Description' => $Description,
        'IsActive' => $IsActive ? 1 : 0,
        'CreatedBy' => $user->Id,
        'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($product)->event('create')->log("Added Provider {$product->Id}.");
        return new self($product);
    }
}
