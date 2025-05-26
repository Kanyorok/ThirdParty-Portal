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
        Schema::table('t_Tenders', function (Blueprint $table) {
            $table->unsignedBigInteger('ItemCategoryId')->after('TenderCategory')->nullable(); // adjust placement if needed

            // Add foreign key constraint if item_categories table exists
            // $table->foreign('item_category_id')
            //       ->references('id')
            //       ->on('item_categories')
            //       ->onDelete('cascade'); // optional: decide if cascade is needed
            
         });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Tenders', function (Blueprint $table) {
            //$table->dropForeign(['ItemCategoryId']);
            $table->dropColumn('ItemCategoryId');
        });
    }
};
