<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PropertyManagement\PropertyType;
use App\Models\Core\CategoryMaster;
use Carbon\Carbon;

class PropertyTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Get category IDs dynamically by name
        $warehouseCategoryId = CategoryMaster::where('Name', 'warehouse')->value('Id');
        $commercialCategoryId = CategoryMaster::where('Name', 'Commercial')->value('Id');
        $residentialCategoryId = CategoryMaster::where('Name', 'Residential')->value('Id');

        $propertyTypes = [
            [
                'PropertyTypeName' => 'Cold Storage Warehouse',
                'PropertyCategoryId' => $warehouseCategoryId,
                'Description' => 'A warehouse used for cold storage',
                'CreatedBy' => 2,
                'ModifiedBy' => 2,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ],
            [
                'PropertyTypeName' => 'Office Block',
                'PropertyCategoryId' => $commercialCategoryId,
                'Description' => 'Multi-floor commercial office building',
                'CreatedBy' => 2,
                'ModifiedBy' => 2,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ],
            [
                'PropertyTypeName' => 'High-Rise Apartment',
                'PropertyCategoryId' => $residentialCategoryId,
                'Description' => 'Residential apartment block with amenities',
                'CreatedBy' => 2,
                'ModifiedBy' => 2,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ],
        ];

        PropertyType::insert($propertyTypes);
    }
}
