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
        Schema::create('t_BudgetTopDownData', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('TopDownID')->constrained('t_BudgetTopDown', 'Id');
            $table->foreignId('BranchID')->constrained('t_Branches', 'Id');
            $table->decimal('AllocationPercentage', 4, 2);
            
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BudgetTopDownData');
    }
};
