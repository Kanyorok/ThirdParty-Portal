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
        // 1. Identify IDs
        // "Old" Stock = 9, "New" Stock = 11
        // "Old" Intangibles = 10, "New" Intangible = 12

        $oldStock = 9;
        $newStock = 11;

        $oldIntangible = 10;
        $newIntangible = 12;

        // 2. Re-point ItemCategories from New to Old
        DB::table('t_ItemCategories')
            ->where('ItemTypeId', $newStock)
            ->update(['ItemTypeId' => $oldStock]);

        DB::table('t_ItemCategories')
            ->where('ItemTypeId', $newIntangible)
            ->update(['ItemTypeId' => $oldIntangible]);

        // 3. Re-point Items from New to Old
        DB::table('t_Items')
            ->where('ItemType', $newStock)
            ->update(['ItemType' => $oldStock]);

        DB::table('t_Items')
            ->where('ItemType', $newIntangible)
            ->update(['ItemType' => $oldIntangible]);

        echo "Consolidated ItemTypes: Configured everything to use Stock ($oldStock) and Intangibles ($oldIntangible).\n";

        // 4. Update Tender Categories (Just in case)
        // Ensure GOODS (2) maps to Stock (9) -> Already does.
        // Ensure WORKS (3) maps to Stock (9) -> Already does.
        // We can safely delete the accidental new types (11, 12) or leave them.

        // Let's soft delete them to avoid confusion
        // Assuming t_ItemTypes doesn't have DeletedOn, but has Active=0?
        DB::table('t_ItemTypes')->whereIn('Id', [$newStock, $newIntangible])->update(['Active' => 0]);
        echo "Deactivated duplicate ItemTypes ($newStock, $newIntangible).\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse
    }
};
