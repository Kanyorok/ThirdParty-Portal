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
            $table->id('Id');
            $table->foreignId('ModuleID')->constrained('t_Modules','ModuleID');
            $table->foreignId('TransactionTypeID', 100)->constrained('t_FinanceTransactionTypes','Id');
            $table->unsignedInteger('DebitGLAccountID')->constrained('t_FinanceGLAccounts','Id');
            $table->unsignedInteger('CreditGLAccountID')->constrained('t_FinanceGLAccounts','Id');
            $table->boolean('IsActive')->default(1);

            // User tracking
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
        Schema::dropIfExists('t_FinanceGlTransactionsMapping');
    }
};
