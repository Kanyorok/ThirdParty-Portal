<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PropertyManagement\PropertyUnit;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use Carbon\Carbon;

class PropertyUnitSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Fetch the property
        $property = PropertyRegistry::where('PropertyName', 'Kilimani Towers')->first();
        if (!$property) {
            $this->command->warn('Property "Kilimani Towers" not found. Skipping PropertyUnit seeding.');
            return;
        }

        // Fetch blocks and floors of the property
        $blocks = PropertyBlock::where('PropertyID', $property->Id)->get();

        foreach ($blocks as $block) {
            $floors = PropertyFloor::where('BlockID', $block->Id)->get();

            foreach ($floors as $floor) {
                // Seed 2 units per floor
                for ($i = 1; $i <= 2; $i++) {
                    PropertyUnit::create([
                        'PropertyID' => $property->Id,
                        'BlockID' => $block->Id,
                        'FloorID' => $floor->Id,
                        'UnitCode' => $block->BlockName . '-' . $floor->FloorLabel . '-U' . $i,
                        'UnitSize' => rand(40, 120), // size in square meters
                        'IsRentable' => true,
                        'CurrentStatus' => '1',
                        'Remarks' => 'Standard unit',
                        'CreatedBy' => 2,
                        'ModifiedBy' => 2,
                        'CreatedOn' => $now,
                        'ModifiedOn' => $now,
                    ]);
                }
            }
        }
    }
}
