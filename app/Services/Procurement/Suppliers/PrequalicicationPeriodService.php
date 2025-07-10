<?php

namespace App\Services\Procurement\Suppliers;

use App\Enums\Procurement\PrequalificationPeriodEnum;
use App\Models\Auth\User;
use App\Models\Procurement\PrequalificationPeriod;
use DateTime;

class PrequalicicationPeriodService
{
    protected PrequalificationPeriod $period;

    public function __construct(PrequalificationPeriod $prequalificationPeriod)
    {
        $this->period = $prequalificationPeriod;
    }

    /**
     * Create a new PrequalificationPeriod
     */
    public static function create(
        string                     $Title,
        string                     $Description,
        DateTime                   $StartDate,
        DateTime                   $EndDate,
        int                        $MaxVendors,
        PrequalificationPeriodEnum $Status,
        User                       $user
    ): self
    {
        $newPeriod = PrequalificationPeriod::create([
            'Title' => $Title,
            'Description' => $Description,
            'StartDate' => $StartDate,
            'EndDate' => $EndDate,
            'MaxVendors' => $MaxVendors,
            'Status' => $Status->value,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()
            ->causedBy($user->Id)
            ->performedOn($newPeriod)
            ->event('create')
            ->log("Created Prequalification Period #{$newPeriod->Id}");

        return new self($newPeriod);
    }

    /**
     * Update an existing PrequalificationPeriod
     */
    public function update(
        PrequalificationPeriod     $period,
        string                     $Title,
        string                     $Description,
        DateTime                   $StartDate,
        DateTime                   $EndDate,
        int                        $MaxVendors,
        PrequalificationPeriodEnum $Status,
        User                       $user
    ): self
    {
        $this->period = $period;

        $this->period->update([
            'Title' => $Title,
            'Description' => $Description,
            'StartDate' => $StartDate,
            'EndDate' => $EndDate,
            'MaxVendors' => $MaxVendors,
            'Status' => $Status->value,
            'ModifiedBy' => $user->Id,
        ]);

        activity()
            ->causedBy($user->Id)
            ->performedOn($this->period)
            ->event('update')
            ->log("Updated Prequalification Period #{$this->period->Id}");

        return $this;
    }


    /**
     * Delete a PrequalificationPeriod
     */
    public function delete(User $user): void
    {
        $id = $this->period->Id;

        $this->period->delete();

        activity()
            ->causedBy($user->Id)
            ->performedOn($this->period)
            ->event('delete')
            ->log("Deleted Prequalification Period #{$id}");
    }

    /**
     * Access the current period model
     */
    public function getModel(): PrequalificationPeriod
    {
        return $this->period;
    }
}
