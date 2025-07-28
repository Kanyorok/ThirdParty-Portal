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
        Schema::create('t_FinanceJournalLines', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('JournalEntryId')->constrained('t_FinanceJournalEntries', 'Id');
            $table->foreignId('GLAccountID')->constrained('t_FinanceGLAccounts');
            $table->foreignId('BranchID')->nullable()->constrained('t_Branches');
            $table->foreignId('DepartmentID')->nullable()->constrained('t_Departments');
            $table->decimal('Debit', 15, 2)->default(0);
            $table->decimal('Credit', 15, 2)->default(0);
            $table->decimal('Amount', 15, 2)->default(0);
            $table->boolean('IsDebit'); //To help track if trx is a debit or credit
            $table->text('Narration')->nullable();

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
        Schema::dropIfExists('t_FinanceJournalLines');
    }
};
