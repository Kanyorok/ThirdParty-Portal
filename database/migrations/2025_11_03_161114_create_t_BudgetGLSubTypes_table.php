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
        Schema::create('t_BudgetGLSubTypes', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('AccountID')->nullable()->index();
            $table->string('Description');
            $table->string('CurrencyID', 10)->nullable();
            $table->string('GLAccountTypeID', 5)->nullable()->index();
            $table->bigInteger('GLSubAccountTypeID')->nullable()->index();
            $table->bigInteger('GLTypeGroupID')->nullable()->index();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_budget__3214ec0797c9ebfc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BudgetGLSubTypes');
    }
};
