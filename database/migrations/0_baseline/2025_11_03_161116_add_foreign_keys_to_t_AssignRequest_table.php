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
        Schema::table('t_AssignRequest', function (Blueprint $table) {
            $table->foreign(['AssignmentType'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['InternalTechnician'])->references(['Id'])->on('t_Employees')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PrequalifiedVendor'])->references(['Id'])->on('t_Suppliers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PriorityLevel'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['RequestNumber'])->references(['Id'])->on('t_MaintenanceRequest')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_AssignRequest', function (Blueprint $table) {
            $table->dropForeign('t_assignrequest_assignmenttype_foreign');
            $table->dropForeign('t_assignrequest_createdby_foreign');
            $table->dropForeign('t_assignrequest_deletedby_foreign');
            $table->dropForeign('t_assignrequest_internaltechnician_foreign');
            $table->dropForeign('t_assignrequest_modifiedby_foreign');
            $table->dropForeign('t_assignrequest_prequalifiedvendor_foreign');
            $table->dropForeign('t_assignrequest_prioritylevel_foreign');
            $table->dropForeign('t_assignrequest_requestnumber_foreign');
        });
    }
};
