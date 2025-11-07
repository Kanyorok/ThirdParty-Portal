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
        Schema::table('t_Employees', function (Blueprint $table) {
            $table->foreign(['BranchId'])->references(['Id'])->on('t_Branches')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DepartmentId'])->references(['Id'])->on('t_Departments')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ImageId'])->references(['ImageID'])->on('t_Images')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Employees', function (Blueprint $table) {
            $table->dropForeign('t_employees_branchid_foreign');
            $table->dropForeign('t_employees_createdby_foreign');
            $table->dropForeign('t_employees_deletedby_foreign');
            $table->dropForeign('t_employees_departmentid_foreign');
            $table->dropForeign('t_employees_imageid_foreign');
            $table->dropForeign('t_employees_modifiedby_foreign');
        });
    }
};
