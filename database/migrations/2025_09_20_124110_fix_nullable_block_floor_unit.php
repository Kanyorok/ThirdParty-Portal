<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_MaintenanceRequest', function (Blueprint $table) {
            // First drop existing foreign key constraints
            $table->dropForeign(['Block']);
            $table->dropForeign(['Floor']);
            $table->dropForeign(['Unit']);

            // Then modify the columns to be nullable
            $table->unsignedBigInteger('Block')->nullable()->change();
            $table->unsignedBigInteger('Floor')->nullable()->change();
            $table->unsignedBigInteger('Unit')->nullable()->change();

            // Re-add the foreign keys without forcing NOT NULL
            $table->foreign('Block')->references('Id')->on('t_PropertyBlock');
            $table->foreign('Floor')->references('Id')->on('t_PropertyFloor');
            $table->foreign('Unit')->references('Id')->on('t_PropertyUnit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_MaintenanceRequest', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['Block']);
            $table->dropForeign(['Floor']);
            $table->dropForeign(['Unit']);
        });

        // Ensure no NULLs remain before altering columns
        DB::table('t_MaintenanceRequest')
            ->whereNull('Block')->update(['Block' => 1]); // <-- use valid Id
        DB::table('t_MaintenanceRequest')
            ->whereNull('Floor')->update(['Floor' => 1]);
        DB::table('t_MaintenanceRequest')
            ->whereNull('Unit')->update(['Unit' => 1]);

        Schema::table('t_MaintenanceRequest', function (Blueprint $table) {
            $table->unsignedBigInteger('Block')->nullable(false)->change();
            $table->unsignedBigInteger('Floor')->nullable(false)->change();
            $table->unsignedBigInteger('Unit')->nullable(false)->change();

            $table->foreign('Block')->references('Id')->on('t_PropertyBlock');
            $table->foreign('Floor')->references('Id')->on('t_PropertyFloor');
            $table->foreign('Unit')->references('Id')->on('t_PropertyUnit');
        });
    }

};
