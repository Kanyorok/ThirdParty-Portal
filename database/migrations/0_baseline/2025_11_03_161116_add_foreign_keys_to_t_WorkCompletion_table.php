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
        Schema::table('t_WorkCompletion', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['FinalStatus'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['RequestNumber'])->references(['Id'])->on('t_AssignRequest')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_WorkCompletion', function (Blueprint $table) {
            $table->dropForeign('t_workcompletion_createdby_foreign');
            $table->dropForeign('t_workcompletion_deletedby_foreign');
            $table->dropForeign('t_workcompletion_finalstatus_foreign');
            $table->dropForeign('t_workcompletion_modifiedby_foreign');
            $table->dropForeign('t_workcompletion_requestnumber_foreign');
        });
    }
};
