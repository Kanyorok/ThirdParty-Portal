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
        Schema::table('t_DocumentValidations', function (Blueprint $table) {
            $table->foreign(['ApprovedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DocumentId'])->references(['Id'])->on('t_Documents')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ValidationTypeId'])->references(['Id'])->on('t_DocumentValidationTypes')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_DocumentValidations', function (Blueprint $table) {
            $table->dropForeign('t_documentvalidations_approvedby_foreign');
            $table->dropForeign('t_documentvalidations_createdby_foreign');
            $table->dropForeign('t_documentvalidations_deletedby_foreign');
            $table->dropForeign('t_documentvalidations_documentid_foreign');
            $table->dropForeign('t_documentvalidations_modifiedby_foreign');
            $table->dropForeign('t_documentvalidations_validationtypeid_foreign');
        });
    }
};
