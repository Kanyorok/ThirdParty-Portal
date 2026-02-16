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
        Schema::create('t_FinanceSyncGLAccountsStaging', function (Blueprint $table) {
            $table->id('Id');
            $table->string('GLCode')->unique();
            $table->string('GLName')->nullable();
            $table->string('GLAccountTypeID')->nullable();
            $table->unsignedBigInteger('GLTypeGroupID')->nullable();
            $table->unsignedBigInteger('GLSubAccountTypeID')->nullable();
            $table->bigInteger('ParentGLID')->nullable();
            $table->char('NormalBalance', 2)->nullable();
            $table->boolean('IsControlAccount')->default(0);
            $table->boolean('IsPostingAccount')->default(1);
            $table->string('CBSAccountCode', 50)->nullable();
            $table->string('BranchID')->nullable();
            $table->string('GLAccountTypeValue', 100)->nullable();
            $table->string('GLTypeGroupIDValue', 100)->nullable();
            $table->string('GLTypeGroupValue', 100)->nullable();
            $table->string('GLSubAccountTypeIDValue', 100)->nullable();
            $table->string('GLDigits', 100)->nullable();
            $table->string('Description', 255)->nullable();
            $table->boolean('IsActive')->default(1);
            $table->integer('CurrencyID')->default(56);
            $table->string('Source', 100)->nullable();
            $table->string('SourceTable', 255)->nullable();
            $table->boolean('IsSynced')->default(false);
            $table->foreignId('CreatedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn')->nullable();
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn')->nullable();
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('DeletedOn')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FinanceSyncGLAccountsStaging');
    }
};
