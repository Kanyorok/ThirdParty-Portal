<?php
// filepath: database/migrations/xxxx_xx_xx_xxxxxx_make_itemcode_nullable_in_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_Items', function (Blueprint $table) {
            $table->dropUnique('t_items_itemcode_unique'); // Drop the unique index first
        });

        Schema::table('t_Items', function (Blueprint $table) {
            $table->string('ItemCode')->nullable()->change();
            $table->unique('ItemCode'); // Re-add the unique index if needed
        });
    }

    public function down(): void
    {
        Schema::table('t_Items', function (Blueprint $table) {
            $table->dropUnique('t_items_itemcode_unique'); // Drop unique index first
        });

        Schema::table('t_Items', function (Blueprint $table) {
            $table->string('ItemCode')->nullable(false)->change(); // Change to not nullable
            $table->unique('ItemCode'); // Re-add the unique constraint
        });
    }
};
