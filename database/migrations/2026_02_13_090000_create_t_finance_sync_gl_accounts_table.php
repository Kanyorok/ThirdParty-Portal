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
        Schema::create('t_FinanceSyncGLAccounts', function (Blueprint $table) {
            $table->id('Id');
            $table->string('GLAccountTypeID');
            $table->foreignId('GLTypeGroupID')->constrained('t_FinanceGLTypeGroups', 'Id');
            $table->foreignId('GLSubAccountTypeID')->constrained('t_FinanceGLSubAccountTypes', 'Id');
            $table->bigInteger('ParentGLID')->nullable();
            $table->string('GLCode')->nullable();
            $table->string('GLName');
            $table->char('NormalBalance', 2)->nullable();
            $table->boolean('IsControlAccount')->default(0);
            $table->boolean('IsPostingAccount')->default(1);
            $table->string('CBSAccountCode', 50)->nullable();
            $table->string('BranchID')->nullable();
            $table->string('Description', 255)->nullable();
            $table->boolean('IsActive')->default(1);

            // Segmentation helpers
            $table->string('GLAccountTypeValue', 100)->nullable();
            $table->string('GLTypeGroupIDValue', 100)->nullable();
            $table->string('GLTypeGroupValue', 100)->nullable();
            $table->string('GLSubAccountTypeIDValue', 100)->nullable();
            $table->string('GLDigits', 100)->nullable(); // number of digits e.g. 3 => 001

            $table->integer('CurrencyID')->default(56);
            $table->boolean('IsSynced')->default(false);

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
        Schema::dropIfExists('t_FinanceSyncGLAccounts');
    }
};
