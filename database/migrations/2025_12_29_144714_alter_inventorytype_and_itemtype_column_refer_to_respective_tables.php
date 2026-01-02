<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_Items', function (Blueprint $table) {
            // First, drop the existing foreign key constraints
            $table->dropForeign(['ItemType']);
            $table->dropForeign(['InventoryType']);
            
            // Drop the existing columns
            $table->dropColumn('ItemType');
            $table->dropColumn('InventoryType');
        });

        Schema::table('t_Items', function (Blueprint $table) {
            // Re-add the columns with new foreign key constraints
            $table->foreignId('InventoryType')->nullable()->constrained('t_InventoryTypes');
            $table->foreignId('ItemType')->nullable()->constrained('t_ItemTypes');
        });
    }

    public function down(): void
    {
        Schema::table('t_Items', function (Blueprint $table) {
            // Drop the new foreign key constraints
            $table->dropForeign(['ItemType']);
            $table->dropForeign(['InventoryType']);
            
            // Drop the columns
            $table->dropColumn('ItemType');
            $table->dropColumn('InventoryType');
        });

        Schema::table('t_Items', function (Blueprint $table) {
            // Revert back to the original structure with t_CodeDetails
            $table->foreignId('ItemType')->nullable()->constrained('t_CodeDetails', 'ID');

            $table->foreignId('InventoryType')->nullable()->constrained('t_CodeDetails', 'ID');
        });
    }
};