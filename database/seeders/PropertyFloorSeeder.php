<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyBlock;
use Carbon\Carbon;

class PropertyFloorSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Fetch a property
        $property = PropertyRegistry::where('PropertyName', 'Kilimani Towers')->first();
        if (!$property) {
            $this->command->warn('Property "Kilimani Towers" not found. Skipping PropertyFloor seeding.');
            return;
        }

        // Fetch blocks of that property
        $blocks = PropertyBlock::where('PropertyID', $property->Id)->get();
        if ($blocks->isEmpty()) {
            $this->command->warn('No blocks found for "Kilimani Towers". Skipping PropertyFloor seeding.');
            return;
        }

        foreach ($blocks as $block) {
            // Create 3 floors for each block
            foreach (['Ground Floor', '1st Floor', '2nd Floor'] as $index => $label) {
                PropertyFloor::create([
                    'PropertyID' => $property->Id,
                    'BlockID' => $block->Id,
                    'FloorLabel' => $label,
                    'FloorNotes' => 'Notes for ' . $label . ' of ' . $block->BlockName,
                    'CreatedBy' => 2,
                    'ModifiedBy' => 2,
                    'CreatedOn' => $now,
                    'ModifiedOn' => $now,
                ]);
            }
        }
    }
}
