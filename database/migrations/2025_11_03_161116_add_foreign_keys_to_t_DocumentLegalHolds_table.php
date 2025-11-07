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
        Schema::table('t_DocumentLegalHolds', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DocId'])->references(['Id'])->on('t_Documents')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['LegalHoldId'])->references(['Id'])->on('t_DMSLegalHolds')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_DocumentLegalHolds', function (Blueprint $table) {
            $table->dropForeign('t_documentlegalholds_createdby_foreign');
            $table->dropForeign('t_documentlegalholds_deletedby_foreign');
            $table->dropForeign('t_documentlegalholds_docid_foreign');
            $table->dropForeign('t_documentlegalholds_legalholdid_foreign');
            $table->dropForeign('t_documentlegalholds_modifiedby_foreign');
        });
    }
};
