<?php

namespace App\Services\Insurance\Customers;

use App\Models\Auth\User;
use App\Models\Insurance\BancassuranceCustomersContacts;
use App\Models\Insurance\BancassuranceCustomers;
use App\Models\Core\CodeDetail;
use App\Models\HRM\Employee;
use DateTime;

class BancassuranceCustomersContactsService
{
    /**
     * Create a new class instance.
     */
    public static function create(
        BancassuranceCustomers $CustomerID,
        DateTime $ContactDate,
        CodeDetail $ContactType,
        string $Summary,
        string  $Notes,
        Employee $HandledBy,
        User   $user
    ): self
    {
        $log = BancassuranceCustomersContacts::create([
        'CustomerID' => $CustomerID->Id,
        'ContactDate' => $ContactDate,
        'ContactType' => $ContactType->ID,
        'Summary' => $Summary,
        'Notes' => $Notes,
        'HandledBy' => $HandledBy->Id,
        'CreatedBy' => $user->Id,
        'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($log)->event('create')->log("Added Customer Contacts {$log->Id}.");
        return new self($log);
    }
}