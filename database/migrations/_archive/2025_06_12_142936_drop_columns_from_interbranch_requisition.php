<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_InterBranchRequisitionItems', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['Category']);
            $table->dropForeign(['Subcategory']);
            $table->dropForeign(['UOM']);

            // Then drop the columns
            $table->dropColumn(['Category', 'Subcategory', 'UOM']);
        });
    }

    public function down(): void
    {
        Schema::table('t_InterBranchRequisitionItems', function (Blueprint $table) {
            // Add columns back
            $table->foreignId('Category')->constrained('t_ItemCategories', 'Id');
            $table->foreignId('Subcategory')->nullable()->constrained('t_ItemCategories', 'Id');
            $table->foreignId('UOM')->constrained('t_UOM', 'Id');
        });
    }
};
