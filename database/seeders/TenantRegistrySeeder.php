<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\Core\CodeDetail;
use Carbon\Carbon;

class TenantRegistrySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Fetch a sample tenant type
        $tenantType = CodeDetail::where('CodeID', 'TenantType')
                                ->where('Description', 'Individual')
                                ->first();

        if (!$tenantType) {
            $this->command->warn('TenantType "Individual" not found in CodeDetail. Skipping PropertyNewTenant seeding.');
            return;
        }

        // Create sample tenants
        PropertyNewTenant::create([
            'TenantType'        => $tenantType->ID,
            'TenantName'        => 'John Doe',
            'IDRegistrationNo'  => 'ID12345678',
            'PhoneNumber'       => '0722123456',
            'EmailAddress'      => 'johndoe@example.com',
            'Nationality'       => 'Kenyan',
            'PostalAddress'     => 'P.O. Box 1234-00100 Nairobi',
            'Remarks'           => 'Good payment history',
            'IsActive'          => true,
            'CreatedBy'         => 2,
            'ModifiedBy'        => 2,
            'CreatedOn'         => $now,
            'ModifiedOn'        => $now,
        ]);

        PropertyNewTenant::create([
            'TenantType'        => $tenantType->ID,
            'TenantName'        => 'Jane Wanjiku',
            'IDRegistrationNo'  => 'ID87654321',
            'PhoneNumber'       => '0733344556',
            'EmailAddress'      => 'jane@example.com',
            'Nationality'       => 'Kenyan',
            'PostalAddress'     => 'P.O. Box 5678-00200 Nairobi',
            'Remarks'           => 'New tenant',
            'IsActive'          => true,
            'CreatedBy'         => 2,
            'ModifiedBy'        => 2,
            'CreatedOn'         => $now,
            'ModifiedOn'        => $now,
        ]);
    }
}
