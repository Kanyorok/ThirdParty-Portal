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
        Schema::create('t_InterBranchRequisitionItems', function (Blueprint $table) {
           $table->id('Id');
           $table->foreignId('RequisitionId')->constrained('t_InterBranchRequisition', 'Id')->onDelete('cascade');
           $table->foreignId('Category')->constrained('t_ItemCategories', 'Id');
           $table->foreignId('Subcategory')->nullable()->constrained('t_ItemCategories', 'Id');
           $table->foreignId('Item')->constrained('t_Items', 'Id');
           $table->foreignId('UOM')->constrained('t_UOM', 'Id');
           $table->integer('RequestedQty');
           $table->string('Remarks')->nullable();
           $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_InterBranchRequisitionItems');
    }
};
