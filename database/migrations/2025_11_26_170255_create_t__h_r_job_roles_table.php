<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRJobRoles', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Code', 50)->unique();
            $table->string('Name', 150);
            $table->unsignedBigInteger('GradeID')->nullable(); // FK to t_HRJobGrades
            $table->unsignedBigInteger('DepartmentID')->nullable(); // FK to t_Departments if exists
            $table->string('Description', 255)->nullable();
            $table->boolean('IsActive')->default(1);

            $table->unsignedBigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('GradeID')->references('Id')->on('t_HRJobGrades');
            $table->foreign('DepartmentID')->references('Id')->on('t_Departments');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRJobRoles');
    }
};
