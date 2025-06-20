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
        Schema::create('t_BudgetDriverProjectionsData', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('BudgetDriverProjectionsID')->constrained('t_BudgetDriverProjections','Id');
            $table->foreignId('ProductID')->constrained('t_BudgetProductTypes','Id');
            $table->integer('Volume');
            $table->decimal('Value', 18, 2);

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
        Schema::dropIfExists('t_BudgetDriverProjectionsData');
    }
};
