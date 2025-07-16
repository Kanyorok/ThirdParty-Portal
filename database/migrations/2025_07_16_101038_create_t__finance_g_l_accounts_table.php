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
            $table->id('Id');
            $table->bigInteger('GLAccountTypeID');
            $table->foreignId('GLTypeGroupID')->constrained('t_FinanceGLTypeGroups', 'Id');
            $table->foreignId('GLSubAccountTypeID')->constrained('t_FinanceGLSubAccountTypes', 'Id');
            $table->bigInteger('ParentGLID')->nullable();
            $table->string('GLCode')->unique();
            $table->string('GLName');
            $table->char('NormalBalance', 2)->nullable();
            $table->boolean('IsControlAccount')->default(0);
            $table->boolean('IsPostingAccount')->default(1);
            $table->string('CBSAccountCode', 50)->nullable();
            $table->bigInteger('BranchID')->nullable();
            $table->string('Description', 255)->nullable();
            $table->boolean('IsActive')->default(1);

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
        Schema::dropIfExists('t_FinanceGLAccounts');
    }
};
