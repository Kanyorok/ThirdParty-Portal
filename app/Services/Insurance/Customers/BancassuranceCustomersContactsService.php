<?php

namespace App\Services\Insurance\Customers;

use App\Models\Auth\User;
use App\Models\Insurance\BancassuranceCustomerContact;
use App\Models\Insurance\BancassuranceCustomer;
use App\Models\Core\Approval\CodeDetail;
use App\Models\HR\Employee;
use DateTime;

class BancassuranceCustomersContactsService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public BancassuranceCustomerContact $log)
    {
    }

    public static function create(
        BancassuranceCustomer $CustomerID,
        DateTime              $ContactDate,
        CodeDetail            $ContactType,
        string                $Summary,
        string                $Notes,
        Employee              $HandledBy,
        User                  $user
    ): self
    {
        $log = BancassuranceCustomerContact::create([
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
