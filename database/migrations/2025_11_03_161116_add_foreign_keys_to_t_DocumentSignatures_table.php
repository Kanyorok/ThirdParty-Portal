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
        Schema::table('t_DocumentSignatures', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DocumentId'])->references(['Id'])->on('t_Documents')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['SignatureId'])->references(['Id'])->on('t_DMSSignatures')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_DocumentSignatures', function (Blueprint $table) {
            $table->dropForeign('t_documentsignatures_createdby_foreign');
            $table->dropForeign('t_documentsignatures_deletedby_foreign');
            $table->dropForeign('t_documentsignatures_documentid_foreign');
            $table->dropForeign('t_documentsignatures_modifiedby_foreign');
            $table->dropForeign('t_documentsignatures_signatureid_foreign');
        });
    }
};
