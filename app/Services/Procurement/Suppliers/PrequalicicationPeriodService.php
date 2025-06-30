<?php

namespace App\Services\Procurement\Suppliers;

use App\Models\Auth\User;
use App\Models\Procurement\PrequalificationPeriod;
use DateTime;

class PrequalicicationPeriodService
{
    /**
     * Create a new class instance.
     */
    public function __construct(PrequalificationPeriod $prequalificationPeriod)
    {
    }
    Public static function create(
        string $Title,
        string $Description,
        DateTime $StartDate,
        DateTime $EndDate,
        User $user
    ): self {
        $newPeriod = PrequalificationPeriod::create([
            'Title' => $Title,
            'Description' => $Description,
            'StartDate' => $StartDate,
            'EndDate' => $EndDate,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)
        ->performedOn($newPeriod)
        ->event('create')
        ->log("Added New Lease {$newPeriod->Id}.");
        return new self($newPeriod);
    }
}
