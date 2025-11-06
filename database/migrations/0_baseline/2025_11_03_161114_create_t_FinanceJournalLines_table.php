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
            $table->bigIncrements('Id');
            $table->bigInteger('JournalEntryId');
            $table->bigInteger('GLAccountID');
            $table->bigInteger('BranchID')->nullable();
            $table->bigInteger('DepartmentID')->nullable();
            $table->decimal('Debit', 15)->default(0);
            $table->decimal('Credit', 15)->default(0);
            $table->decimal('Amount', 15)->default(0);
            $table->boolean('IsDebit');
            $table->text('Narration')->nullable();
            $table->string('SystemDescription')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec07dcd35db7');
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
