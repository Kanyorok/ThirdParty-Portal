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
        Schema::create('t_FinanceGLBranch', function (Blueprint $table) {
            $table->id('Id');
            $table->bigInteger('LastTransactionId ')->nullable();
            $table->foreignId('GLAccountID')->constrained('t_FinanceGLAccounts','Id');
            $table->foreignId('BranchID')->nullable()->constrained('t_Branches', 'Id');
            $table->string('GLCode');
            $table->decimal('Balance',18,5)->default(0.00000);
            $table->decimal('LocalBalance', 18, 5)->default(0.00000);
            $table->decimal('ForeignBalance', 18, 5)->default(0.00000);
            $table->string('GLAccountType',5);//Store A for Assets
            $table->boolean('IsActive')->default(true);
            $table->integer('BankID')->nullable();

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
        Schema::dropIfExists('t_FinanceGLBranch');
    }
};
