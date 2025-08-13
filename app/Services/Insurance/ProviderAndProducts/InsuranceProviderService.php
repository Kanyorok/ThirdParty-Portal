<?php

namespace App\Services\Insurance\ProviderAndProducts;

use App\Models\Auth\User;
use App\Models\Insurance\InsuranceProvider;

class InsuranceProviderService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public InsuranceProvider $customer)
    {
    }

    public static function create(
        string $Name,
        string $Country,
        string $ContactPerson,
        string $Email,
        string $Phone,
        bool $IsActive,
        User $user
    ): self {

        // Extract only numeric part after the dash and cast to INT safely
        $lastInsuranceProviderNO = InsuranceProvider::withTrashed()
            ->selectRaw("
                MAX(
                    TRY_CAST(
                        RIGHT(InsuranceProviderNO, LEN(InsuranceProviderNO) - CHARINDEX('-', InsuranceProviderNO))
                        AS INT
                    )
                ) as max_number
            ")
            ->value('max_number');

        // Increment number or start from 1
        $nextNumber = $lastInsuranceProviderNO ? $lastInsuranceProviderNO + 1 : 1;

        // Generate the next InsuranceProviderNO
        $InsuranceProviderNO = 'InsuranceProviderNO-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        // Create the provider
        $provider = InsuranceProvider::create([
            'InsuranceProviderNO' => $InsuranceProviderNO,
            'Name' => $Name,
            'Country' => $Country,
            'ContactPerson' => $ContactPerson,
            'Email' => $Email,
            'Phone' => $Phone,
            'IsActive' => $IsActive ? 1 : 0,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        // Log activity
        activity()
            ->causedBy($user->Id)
            ->performedOn($provider)
            ->event('create')
            ->log("Added Provider {$provider->Id}.");

        return new self($provider);
    }
}
