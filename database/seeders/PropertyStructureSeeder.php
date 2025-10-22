<?php

namespace Database\Seeders;

use App\Models\Core\Country;
use App\Models\Core\Locality;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyUnit;
use Illuminate\Database\Seeder;
use App\Models\Auth\User;

class PropertyStructureSeeder extends Seeder
{
    public function run(): void
    {
        $countryId = Country::query()->value('Id') ?? 1;
        $cityId = Locality::query()->where('CountryId', $countryId)->value('ID') ?? Locality::query()->value('ID');
        $now = now();
        $userId = User::query()->value('Id') ?? 1;

        for ($i = 1; $i <= 3; $i++) {
            $property = PropertyRegistry::create([
                'PropertyName' => "Demo Property {$i}",
                'PropertyCode' => 'PR' . str_pad((string)$i, 3, '0', STR_PAD_LEFT),
                'PropertyType' => 1, // assumes seeded PropertyType with Id 1
                'Category' => 1, // assumes seeded Category with Id 1
                'Owner' => 'Seeded Owner',
                'AcquisitionDate' => now()->subYears(5)->toDateString(),
                'Address' => 'Main Street',
                'CountryId' => $countryId,
                'LocationId' => $cityId,
                'PropertyDescription' => 'Seeded property for demo',
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
                'CreatedBy' => $userId,
                'ModifiedBy' => $userId,
            ]);

            // Add Blocks
            for ($b = 1; $b <= 2; $b++) {
                $block = PropertyBlock::create([
                    'PropertyID' => $property->Id,
                    'BlockName' => "Block {$b}",
                    'Description' => 'Seeded block',
                    'CreatedOn' => $now,
                    'ModifiedOn' => $now,
                    'CreatedBy' => $userId,
                    'ModifiedBy' => $userId,
                ]);

                // Add Floors
                for ($f = 0; $f <= 2; $f++) {
                    $floor = PropertyFloor::create([
                        'PropertyID' => $property->Id,
                        'BlockID' => $block->Id,
                        'FloorLabel' => $f === 0 ? 'Ground' : 'Floor ' . $f,
                        'FloorNotes' => 'Seeded floor',
                        'CreatedOn' => $now,
                        'ModifiedOn' => $now,
                        'CreatedBy' => $userId,
                        'ModifiedBy' => $userId,
                    ]);

                    // Add Units per floor
                    for ($u = 1; $u <= 3; $u++) {
                        PropertyUnit::create([
                            'PropertyID' => $property->Id,
                            'BlockID' => $block->Id,
                            'FloorID' => $floor->Id,
                            'UnitCode' => "{$block->Id}-{$floor->Id}-U{$u}",
                            'UnitSize' => 100 + ($u * 10),
                            'IsRentable' => 1,
                            'CurrentStatus' => 1,
                            'Remarks' => 'Seeded unit',
                            'CreatedOn' => $now,
                            'ModifiedOn' => $now,
                            'CreatedBy' => $userId,
                            'ModifiedBy' => $userId,
                        ]);
                    }
                }
            }
        }
    }
}


