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
        Schema::create('t_Departments', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Name');
            $table->string('DepartmentID')->unique();
            $table->string('Description')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('HeadId')->nullable();
            $table->bigInteger('DeputyHeadId')->nullable();

            $table->primary(['Id'], 'pk__t_depart__3214ec07d930cd00');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Departments');
    }
};
