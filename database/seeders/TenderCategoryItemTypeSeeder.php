<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenderCategoryItemTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Resolve common ItemType IDs by name
        $types = DB::table('t_ItemTypes')->pluck('Id', 'TypeName');
        if ($types->isEmpty()) {
            return;
        } // nothing to seed

        $goodsSet = collect([$types['Stock'] ?? null, $types['Consumable'] ?? null, $types['Asset'] ?? null])
            ->filter()->values();
        $servicesSet = collect([$types['Services'] ?? null, $types['Intangibles'] ?? null])
            ->filter()->values();
        $worksSet = collect([$types['Services'] ?? null])
            ->filter()->values();

        $categories = DB::table('t_TenderCategories')->select('Id', 'TenderCategory')->get();
        foreach ($categories as $cat) {
            $target = match (strtoupper((string)$cat->TenderCategory)) {
                'GOODS' => $goodsSet,
                'SERVICES' => $servicesSet,
                'WORKS' => $worksSet,
                default => collect(),
            };
            foreach ($target as $typeId) {
                DB::table('t_TenderCategoryItemTypes')->updateOrInsert(
                    ['TenderCategoryId' => $cat->Id, 'ItemTypeId' => $typeId],
                    ['IsActive' => 1]
                );
            }
        }
    }
}
