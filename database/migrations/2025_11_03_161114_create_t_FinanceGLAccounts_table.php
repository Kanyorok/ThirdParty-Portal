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
        Schema::create('t_FinanceGLAccounts', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('GLAccountTypeID');
            $table->bigInteger('GLTypeGroupID');
            $table->bigInteger('GLSubAccountTypeID');
            $table->bigInteger('ParentGLID')->nullable();
            $table->string('GLCode')->nullable();
            $table->string('GLName');
            $table->char('NormalBalance', 2)->nullable();
            $table->boolean('IsControlAccount')->default(false);
            $table->boolean('IsPostingAccount')->default(true);
            $table->string('CBSAccountCode', 50)->nullable();
            $table->string('BranchID')->nullable();
            $table->string('Description')->nullable();
            $table->boolean('IsActive')->default(true);
            $table->string('GLAccountTypeValue', 100)->nullable();
            $table->string('GLTypeGroupIDValue', 100)->nullable();
            $table->string('GLSubAccountTypeIDValue', 100)->nullable();
            $table->string('GLDigits', 100)->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->integer('CurrencyID')->default(56);
            $table->boolean('IsSynced')->nullable()->default(false);

            $table->primary(['Id'], 'pk__t_financ__3214ec07d83f9215');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FinanceGLAccounts');
    }
};
