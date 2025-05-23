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
        Schema::create('t_BudgetLineLink', function (Blueprint $table) {
            $table->bigIncrements('LinkID');
            $table->unsignedBigInteger('LineItemID');
            $table->string('BudgetLineID');
            $table->decimal('AmountAllocated', 15, 2)->default(0);
            $table->foreignId('LinkedBy')->constrained('t_Users', 'Id');
            $table->timestamp('LinkedDate')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
            $table->foreign('BudgetLineID')->references('BudgetLineID')->on('t_BudgetMaster')->onDelete('cascade');
            $table->foreign('LineItemID')->references('LineItemID')->on('t_PlanLineItem')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BudgetLineLink');
    }
};
