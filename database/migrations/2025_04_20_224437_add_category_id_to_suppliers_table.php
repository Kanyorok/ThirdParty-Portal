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
        Schema::table('t_Suppliers', function (Blueprint $table) {
            $table->unsignedBigInteger('CategoryId')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            // If you're linking to a `categories` table:
            $table->foreign('CategoryId')->references('id')->on('t_ItemCategories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Suppliers', function (Blueprint $table) {
            $table->dropForeign(['CategoryId']);
            $table->dropColumn('CategoryId');
        });
    }
};
