<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_InventoryTypes', function (Blueprint $table) {
            $table->dropColumn('Type');
        });

        Schema::table('t_InventoryTypes', function (Blueprint $table) {
            $table->foreignId('Type')->nullable()
                ->constrained('t_CodeDetails', 'ID');
        });
    }

    public function down(): void
    {
        Schema::table('t_InventoryTypes', function (Blueprint $table) {
            $table->dropForeign(['Type']);
            $table->dropColumn('Type');
        });

        Schema::table('t_InventoryTypes', function (Blueprint $table) {
            $table->string('Type')->after('Id');
        });
    }
};
