<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HREmployeeDocuments', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('EmployeeID');
            $table->string('FileName', 255);
            $table->string('FilePath', 500);
            $table->string('Category', 100)->nullable(); // ID, Contract, Certificate, Other
            $table->string('Description', 255)->nullable();
            $table->unsignedBigInteger('UploadedBy')->nullable();
            $table->dateTime('UploadedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HREmployeeDocuments');
    }
};
