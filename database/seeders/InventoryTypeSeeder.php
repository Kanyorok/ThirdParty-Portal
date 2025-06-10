<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory\InventoryType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InventoryTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $createdBy = 1;

        $types = [
            ['Type' => 'Consumable', 'Status' => true],
            ['Type' => 'Durable', 'Status' => true],
            ['Type' => 'Perishable', 'Status' => true],
            ['Type' => 'Fixed Asset', 'Status' => true],
        ];

        foreach ($types as $type) {
            InventoryType::create([
                'Type' => $type['Type'],
                'Status' => $type['Status'],
                'CreatedBy' => $createdBy,
                'ModifiedBy' => $createdBy,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ]);
        }
    }
}
