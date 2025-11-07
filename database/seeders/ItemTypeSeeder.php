<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory\ItemType;

class ItemTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Optional: Remove existing records (soft delete safe)
        // ItemType::query()->delete(); // <-- Uncomment if you want to clear first

        $now = Carbon::now();
        $createdBy = 1;

        $types = [
            ['TypeName' => 'Stock', 'StockTracked' => true, 'RequiresTagging' => false, 'Active' => true],
            ['TypeName' => 'Asset', 'StockTracked' => false, 'RequiresTagging' => true, 'Active' => true],
            ['TypeName' => 'Consumable', 'StockTracked' => true, 'RequiresTagging' => false, 'Active' => true],
        ];

        foreach ($types as $type) {
            ItemType::create([
                'TypeName' => $type['TypeName'],
                'StockTracked' => $type['StockTracked'],
                'RequiresTagging' => $type['RequiresTagging'],
                'Active' => $type['Active'],
                'CreatedBy' => $createdBy,
                'ModifiedBy' => $createdBy,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ]);
        }
    }
}
