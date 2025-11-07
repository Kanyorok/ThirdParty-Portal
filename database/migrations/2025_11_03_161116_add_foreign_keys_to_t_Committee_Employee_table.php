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
        Schema::table('t_Committee_Employee', function (Blueprint $table) {
            $table->foreign(['CommitteeId'])->references(['Id'])->on('t_Committees')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['EmployeeId'])->references(['Id'])->on('t_Employees')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Committee_Employee', function (Blueprint $table) {
            $table->dropForeign('t_committee_employee_committeeid_foreign');
            $table->dropForeign('t_committee_employee_createdby_foreign');
            $table->dropForeign('t_committee_employee_deletedby_foreign');
            $table->dropForeign('t_committee_employee_employeeid_foreign');
            $table->dropForeign('t_committee_employee_modifiedby_foreign');
        });
    }
};
