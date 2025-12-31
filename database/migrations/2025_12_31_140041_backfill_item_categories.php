<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Iterate through all Item Categories (leaf nodes first ideally, but simple iteration works 
        // if we assume we just want to tag the category based on its direct items)

        $categories = DB::table('t_ItemCategories')->orderBy('Id')->get();

        foreach ($categories as $category) {
            // Find the most frequent ItemType among items in this category
            $dominantItemType = DB::table('t_Items')
                ->where('Category', $category->Id)
                ->whereNull('DeletedOn')
                ->select('ItemType', DB::raw('count(*) as total'))
                ->groupBy('ItemType')
                ->orderByDesc('total')
                ->value('ItemType');

            if ($dominantItemType) {
                DB::table('t_ItemCategories')
                    ->where('Id', $category->Id)
                    ->update(['ItemTypeId' => $dominantItemType]);

                echo "Updated Category ID {$category->Id} ('{$category->Name}') with ItemTypeId: {$dominantItemType}\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed for backfill
    }
};
