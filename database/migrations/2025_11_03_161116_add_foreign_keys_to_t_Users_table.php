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
        Schema::table('t_Users', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['EmployeeId'])->references(['Id'])->on('t_Employees')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['ImageId'])->references(['ImageID'])->on('t_Images')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Users', function (Blueprint $table) {
            $table->dropForeign('t_users_createdby_foreign');
            $table->dropForeign('t_users_deletedby_foreign');
            $table->dropForeign('t_users_employeeid_foreign');
            $table->dropForeign('t_users_imageid_foreign');
            $table->dropForeign('t_users_modifiedby_foreign');
        });
    }
};
