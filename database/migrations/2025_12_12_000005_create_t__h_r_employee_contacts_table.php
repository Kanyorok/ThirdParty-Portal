<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HREmployeeContacts', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('EmployeeID');
            $table->string('Name', 150);
            $table->string('Relation', 100)->nullable();
            $table->string('Phone', 50)->nullable();
            $table->string('Email', 150)->nullable();
            $table->boolean('IsPrimary')->default(false);
            $table->boolean('IsNextOfKin')->default(false);
            $table->boolean('IsEmergency')->default(true);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HREmployeeContacts');
    }
};
