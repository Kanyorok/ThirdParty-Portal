<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyUnit;
use App\Models\Core\CodeDetail;
use Carbon\Carbon;

class PropertyNewLeaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Get tenant
        $tenant = PropertyNewTenant::where('TenantName', 'John Doe')->first();
        if (!$tenant) {
            $this->command->warn('Tenant "John Doe" not found. Skipping lease creation.');
            return;
        }

        // Get unit and its hierarchy
        $unit = PropertyUnit::first(); // or add specific conditions
        if (!$unit) {
            $this->command->warn('No property unit found. Skipping lease creation.');
            return;
        }

        $property = $unit->property;
        $block    = $unit->blocks;
        $floor    = $unit->floors;

        // Get PaymentFrequency CodeDetail (e.g. Monthly)
        $frequency = CodeDetail::where('CodeID', 'PaymentFrequency')
                               ->where('Description', 'Monthly')
                               ->first();

        if (!$frequency) {
            $this->command->warn('Payment frequency "Monthly" not found. Skipping.');
            return;
        }

        // Seed lease
        PropertyNewLease::create([
            'Tenant'            => $tenant->Id,
            'LeaseNumber'       => 'LEASE-' . strtoupper(uniqid()),
            'PropertyID'        => $property->Id,
            'BlockID'           => $block->Id,
            'FloorID'           => $floor->Id,
            'Unit'              => $unit->Id,
            'StartDate'         => $now->toDateString(),
            'EndDate'           => $now->copy()->addYear()->toDateString(),
            'PaymentFrequency'  => $frequency->ID,
            'MonthlyRent'       => 50000,
            'Deposit'           => 50000,
            'DueDay'            => 5,
            'SpecialTerms'      => 'No pets allowed.',
            'IsActive'          => true,
            'CreatedBy'         => 2,
            'ModifiedBy'        => 2,
            'CreatedOn'         => $now,
            'ModifiedOn'        => $now,
        ]);
    }
}
