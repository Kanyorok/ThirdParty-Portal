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
        Schema::create('t_FinanceReverseJournalEntries', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('JournalEntryId');
            $table->bigInteger('OriginalJournalEntryID');
            $table->string('OriginalReferenceNumber');
            $table->date('ReversalDate');
            $table->string('Reason')->nullable();
            $table->string('SystemDescription')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec07a6be3267');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FinanceReverseJournalEntries');
    }
};
