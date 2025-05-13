<?php

use App\Enums\Employee\GenderEnum;
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

        Schema::create('t_Employees', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('EmployeeID')->unique();
            $table->string('FirstName');
            $table->string('LastName');
            $table->string('MiddleName')->nullable();
            $table->string('Email')->unique();
            $table->string('Phone')->nullable();
            $table->string('Address')->nullable();
            $table->date('DateOfBirth')->nullable();
            $table->date('JoinDate');
            $table->foreignId('DepartmentId')->constrained('t_Departments', 'Id');
            $table->foreignId('BranchId')->constrained('t_CRMBranches', 'Id');
            $table->string('JobTitle');
            $table->foreignId('ImageId')->nullable()->constrained('t_CRMImages', 'ImageID');
            $table->string('Gender',1)->default(GenderEnum::Other->value);
            $table->string('MaritalStatus',2)->nullable();

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::table('t_Users', static function (Blueprint $table) {
            $table->foreignId('EmployeeId')->nullable()->constrained('t_Employees', 'Id')->nullOnDelete();
        });

        //seed users
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => 'EmployeeSeeder'
        ]);

        //create employees for users
        Schema::table('t_Users', static function (Blueprint $table) {
            $table->dropIndex('t_users_branchid_index');
            $table->dropColumn(['BranchId', 'Gender']);
            $table->unique('EmployeeId');
        });

        Schema::table('t_Departments', static function (Blueprint $table) {
            $table->foreignId('HeadId')->nullable()->constrained('t_Employees', 'Id');
            $table->foreignId('DeputyHeadId')->nullable()->constrained('t_Employees', 'Id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Users', static function (Blueprint $table) {
            $table->dropUnique('t_users_employeeid_unique');
            $table->dropConstrainedForeignId('EmployeeId');
            $table->char('BranchId', '5')->nullable()->index();
            $table->char('Gender', 1)->default(GenderEnum::Other->value);
        });

        Schema::table('t_Departments', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('HeadId');
            $table->dropConstrainedForeignId('DeputyHeadId');
        });

        Schema::dropIfExists('t_Employees');
    }
};
