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
        Schema::create('t_BudgetGLMaster', function (Blueprint $table) {
            $table->bigIncrements('BudgetGLID');
            $table->string('AccountID', 50);
            $table->string('Description')->nullable();
            $table->string('CurrencyID', 10);
            $table->string('GLAccountTypeID', 10);
            $table->string('GLSubAccountTypeID', 50);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['BudgetGLID'], 'pk__t_budget__f36b91d65421930d');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BudgetGLMaster');
    }
};
