<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyUnit;
use Illuminate\Database\Seeder;

class LeaseManagementSeeder extends Seeder
{
    public function run(): void
    {
        $paymentMonthlyId = CodeDetail::where('CodeID', 'PaymentFrequency')->where('Description', 'Monthly')->value('ID')
            ?? CodeDetail::where('CodeID', 'PaymentFrequency')->value('ID')
            ?? 1;
        $userId = User::query()->value('Id') ?? 1;

        $tenants = PropertyNewTenant::limit(3)->pluck('Id');
        $properties = PropertyRegistry::limit(3)->pluck('Id');

        $units = PropertyUnit::query()
            ->whereIn('PropertyID', $properties)
            ->get()
            ->groupBy('PropertyID');

        $index = 1;
        foreach ($properties as $propertyId) {
            $tenantId = $tenants[$index - 1] ?? $tenants->first();
            $unit = $units[$propertyId]->first();
            if (! $unit) {
                continue;
            }

            // Generate a unique LeaseNumber avoiding duplicate key errors
            $num = $index;
            do {
                $leaseNumber = 'LSE' . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
                $exists = PropertyNewLease::where('LeaseNumber', $leaseNumber)->exists();
                if ($exists) {
                    $num++;
                }
            } while ($exists);

            PropertyNewLease::create([
                'Tenant' => $tenantId,
                'LeaseNumber' => $leaseNumber,
                'PropertyID' => $propertyId,
                // Note: DB FK for BlockID points to t_PropertyRegistry; use propertyId to satisfy FK
                'BlockID' => $propertyId,
                'FloorID' => $unit->FloorID,
                'Unit' => $unit->Id,
                'StartDate' => now()->startOfMonth()->toDateString(),
                'EndDate' => now()->addYear()->endOfMonth()->toDateString(),
                'PaymentFrequency' => $paymentMonthlyId,
                'MonthlyRent' => 50000,
                'Deposit' => 50000,
                'ServiceCharge' => 5000,
                'ParkingFee' => 0,
                'OtherCharges' => 0,
                'DueDay' => 5,
                'SpecialTerms' => 'Seed lease',
                'IsActive' => 1,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
                'CreatedBy' => $userId,
                'ModifiedBy' => $userId,
            ]);

            $index = $num + 1;
        }
    }
}
