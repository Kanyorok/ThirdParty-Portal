<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_Items', function (Blueprint $table) {
            // First, change the column type to match the foreign key
            $table->unsignedBigInteger('CategoryId')->nullable()->change();

            // Then, apply the foreign key constraint
            $table->foreign('CategoryId')
                ->references('id')
                ->on('t_ItemCategories')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_items', function (Blueprint $table) {
            $table->dropForeign(['CategoryId']);

            // Optional: revert the column type back if needed
            $table->unsignedInteger('CategoryId')->nullable()->change();
        });
    }
};
