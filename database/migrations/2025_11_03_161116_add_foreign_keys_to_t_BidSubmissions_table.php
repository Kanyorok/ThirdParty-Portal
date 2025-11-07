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
        Schema::table('t_BidSubmissions', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['OpenedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ResponsivenessCheckedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['SubmissionMode'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BidSubmissions', function (Blueprint $table) {
            $table->dropForeign('t_bidsubmissions_createdby_foreign');
            $table->dropForeign('t_bidsubmissions_deletedby_foreign');
            $table->dropForeign('t_bidsubmissions_modifiedby_foreign');
            $table->dropForeign('t_bidsubmissions_openedby_foreign');
            $table->dropForeign('t_bidsubmissions_responsivenesscheckedby_foreign');
            $table->dropForeign('t_bidsubmissions_submissionmode_foreign');
        });
    }
};
