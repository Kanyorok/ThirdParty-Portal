<?php

namespace Database\Seeders;

use App\Models\Core\Approval\CodeDetail;
use App\Models\PropertyManagement\PropertyLeaseSchedule;
use App\Models\PropertyManagement\PropertyNewLease;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PropertyLeaseScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Get existing lease
        $lease = PropertyNewLease::first(); // You can scope this more specifically
        if (! $lease) {
            $this->command->warn('No lease found. Seed PropertyNewLease first.');

            return;
        }

        // Get payment frequency from CodeDetail
        $frequency = CodeDetail::where('CodeID', 'PaymentFrequency')
            ->where('Description', 'Monthly')
            ->first();

        if (! $frequency) {
            $this->command->warn('Payment frequency "Monthly" not found.');

            return;
        }

        // Create lease schedule
        PropertyLeaseSchedule::create([
            'LeaseNumber' => $lease->Id,
            'TenantId' => $lease->Tenant,
            'PropertyId' => $lease->PropertyID,
            'PaymentFrequency' => $frequency->ID,
            'StartDate' => $lease->StartDate,
            'EndDate' => $lease->EndDate,
            'BaseRent' => $lease->MonthlyRent,
            'ServiceCharge' => 5000,
            'ParkingFee' => 2000,
            'OtherCharges' => 1500,
            'CreatedBy' => 2,
            'ModifiedBy' => 2,
            'CreatedOn' => $now,
            'ModifiedOn' => $now,
        ]);
    }
}
