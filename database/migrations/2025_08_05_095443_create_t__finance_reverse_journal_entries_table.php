<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_FinanceReverseJournalEntries', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('JournalEntryId')->constrained('t_FinanceJournalEntries', 'Id');
            $table->foreignId('OriginalJournalEntryID')->constrained('t_FinanceJournalEntries', 'Id');
            $table->string('OriginalReferenceNumber');
            $table->date('ReversalDate');
            $table->string('Reason', 255)->nullable();
            $table->string('SystemDescription', 255)->nullable();


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
        Schema::dropIfExists('t_FinanceReverseJournalEntries');
    }
};
