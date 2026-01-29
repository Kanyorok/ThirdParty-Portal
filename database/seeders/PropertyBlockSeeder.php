<?php

namespace Database\Seeders;

use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyRegistry;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PropertyBlockSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Fetch a sample property
        $property = PropertyRegistry::where('PropertyName', 'Kilimani Towers')->first();

        if (! $property) {
            $this->command->warn('Property "Kilimani Towers" not found. Skipping PropertyBlock seeding.');

            return;
        }

        // Seed sample blocks for the property
        PropertyBlock::create([
            'PropertyID' => $property->Id,
            'BlockName' => 'Block A',
            'Description' => 'Main residential block with elevator',
            'CreatedBy' => 2,
            'ModifiedBy' => 2,
            'CreatedOn' => $now,
            'ModifiedOn' => $now,
        ]);

        PropertyBlock::create([
            'PropertyID' => $property->Id,
            'BlockName' => 'Block B',
            'Description' => 'Secondary block with fewer units',
            'CreatedBy' => 2,
            'ModifiedBy' => 2,
            'CreatedOn' => $now,
            'ModifiedOn' => $now,
        ]);
    }
}
