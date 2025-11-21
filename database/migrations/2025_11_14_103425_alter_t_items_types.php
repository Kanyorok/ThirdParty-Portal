<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_Items', function (Blueprint $table) {

            $table->dropForeign(['InventoryType']);
            $table->dropForeign(['ItemType']);
            $table->dropColumn('InventoryType');
            $table->dropColumn('ItemType');

        });

        Schema::table('t_Items', function (Blueprint $table) {
            $table->foreignId('ItemType')->nullable()->constrained('t_CodeDetails', 'ID');
            $table->foreignId('InventoryType')->nullable()->constrained('t_CodeDetails', 'ID');
        });
    }

    public function down(): void
    {
        Schema::table('t_Items', function (Blueprint $table) {
            $table->dropForeign(['InventoryType']);
            $table->dropForeign(['ItemType']);
            $table->dropColumn('InventoryType');
            $table->dropColumn('ItemType');
        });

        Schema::table('t_Items', function (Blueprint $table) {
            $table->string('InventoryType')->after('Id');
            $table->string('ItemType')->after('InventoryType');
        });
    }
};
