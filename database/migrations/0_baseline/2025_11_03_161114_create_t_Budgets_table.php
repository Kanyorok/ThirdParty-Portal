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
        Schema::create('t_Budgets', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Name');
            $table->integer('FiscalYear');
            $table->date('From');
            $table->date('To');
            $table->text('Notes');
            $table->string('Status')->default('draft');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->boolean('IsLimitSet')->default(false);
            $table->boolean('IsReAllocated')->default(false);
            $table->text('ApprovalOrRejectionReason')->nullable();

            $table->primary(['Id'], 'pk__t_budget__3214ec07cbddfa5a');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Budgets');
    }
};
