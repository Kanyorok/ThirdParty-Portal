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
        Schema::table('t_FinanceRecurrentJournals', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['JournalEntryId'])->references(['Id'])->on('t_FinanceJournalEntries')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceRecurrentJournals', function (Blueprint $table) {
            $table->dropForeign('t_financerecurrentjournals_createdby_foreign');
            $table->dropForeign('t_financerecurrentjournals_deletedby_foreign');
            $table->dropForeign('t_financerecurrentjournals_journalentryid_foreign');
            $table->dropForeign('t_financerecurrentjournals_modifiedby_foreign');
        });
    }
};
