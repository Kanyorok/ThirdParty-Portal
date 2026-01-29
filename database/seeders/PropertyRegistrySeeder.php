<?php

namespace Database\Seeders;

use App\Models\Core\CategoryMaster;
use App\Models\Core\Locality;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PropertyRegistrySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Get foreign key references
        $residentialTypeId = PropertyType::where('PropertyTypeName', 'High-Rise Apartment')->value('Id');
        $residentialCategoryId = CategoryMaster::where('Name', 'Residential')->value('Id');
        $nairobiLocalityId = Locality::where('Name', 'Nairobi')->value('Id');

        // Add sample property
        PropertyRegistry::create([
            'PropertyName' => 'Kilimani Towers',
            'PropertyCode' => 'PROP-001',
            'PropertyType' => $residentialTypeId,
            'Category' => $residentialCategoryId,
            'Owner' => 'National Housing Corp',
            'AcquisitionDate' => '2018-07-15',
            'Country' => 'Kenya',
            'TownCity' => $nairobiLocalityId,
            'AreaLocality' => 'Kilimani',
            'PropertyDescription' => 'Modern residential apartments located in Nairobi.',
            'CreatedBy' => 2,
            'ModifiedBy' => 2,
            'CreatedOn' => $now,
            'ModifiedOn' => $now,
        ]);
    }
}
