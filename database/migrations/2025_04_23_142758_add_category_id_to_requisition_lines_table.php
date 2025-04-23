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
        Schema::table('t_RequisitionLines', function (Blueprint $table) {
            $table->unsignedBigInteger('CategoryId')->nullable()->after('Item'); // Add CategoryId column
            $table->foreign('CategoryId') // Add foreign key constraint
                  ->references('id')
                  ->on('t_ItemCategories')
                  ->onDelete('set null'); // Set to null if the category is deleted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RequisitionLines', function (Blueprint $table) {
            $table->dropForeign(['CategoryId']); // Drop foreign key constraint
            $table->dropColumn('CategoryId'); // Drop the CategoryId column
        });
    }
};
