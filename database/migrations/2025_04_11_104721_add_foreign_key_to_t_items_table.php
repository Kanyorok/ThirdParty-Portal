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
        Schema::table('t_items', function (Blueprint $table) {
            // First, change the column type to match the foreign key
            $table->unsignedBigInteger('category_id')->nullable()->change();

            // Then, apply the foreign key constraint
            $table->foreign('category_id')
                ->references('id')
                ->on('t_items_categories')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);

            // Optional: revert the column type back if needed
            $table->unsignedInteger('category_id')->nullable()->change();
        });
    }
};
