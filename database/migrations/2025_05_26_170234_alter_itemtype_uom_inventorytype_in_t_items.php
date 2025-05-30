<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_Items', function (Blueprint $table) {
            // Drop the existing string columns
            $table->dropColumn(['ItemType', 'UOM', 'InventoryType']);
        });

        Schema::table('t_Items', function (Blueprint $table) {
            $table->foreignId('ItemType')->nullable()->constrained('t_ItemTypes', 'Id');
            $table->foreignId('UOM')->nullable()->constrained('t_UOM', 'Id');
           $table->foreignId('InventoryType')->nullable()->constrained('t_InventoryTypes', 'Id');
});

    }

    public function down(): void
    {
        Schema::table('t_Items', function (Blueprint $table) {
            $table->dropForeign(['ItemType']);
            $table->dropForeign(['UOM']);
            $table->dropForeign(['InventoryType']);
            $table->dropColumn(['ItemType', 'UOM', 'InventoryType']);

            // Revert to string columns if rolling back
            $table->string('ItemType');
            $table->string('UOM');
            $table->string('InventoryType');
        });
    }
};
