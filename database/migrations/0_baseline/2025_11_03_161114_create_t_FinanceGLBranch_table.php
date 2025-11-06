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
            $table->bigIncrements('Id');
            $table->bigInteger('LastTransactionId ')->nullable();
            $table->bigInteger('GLAccountID');
            $table->bigInteger('BranchID')->nullable();
            $table->string('GLCode');
            $table->decimal('Balance', 18, 5)->default(0);
            $table->decimal('LocalBalance', 18, 5)->default(0);
            $table->decimal('ForeignBalance', 18, 5)->default(0);
            $table->string('GLAccountType', 5);
            $table->boolean('IsActive')->default(true);
            $table->integer('BankID')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec070a46eef4');
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
