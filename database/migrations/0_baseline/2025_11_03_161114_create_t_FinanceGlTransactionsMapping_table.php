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
        Schema::create('t_FinanceGlTransactionsMapping', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('ModuleID');
            $table->bigInteger('TransactionTypeID');
            $table->integer('DebitGLAccountID');
            $table->integer('CreditGLAccountID');
            $table->boolean('IsActive')->default(true);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec07e3803d09');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FinanceGlTransactionsMapping');
    }
};
