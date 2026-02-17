<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_HRGratuities', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->unsignedBigInteger('EmployeeID');
            $table->integer('Year');
            $table->decimal('RatePercent', 6, 2)->default(0);
            $table->decimal('GrossPay', 18, 2)->default(0);
            $table->decimal('Amount', 18, 2)->default(0);
            $table->string('Status', 20)->default('Pending');
            $table->dateTime('PaidOn')->nullable();
            $table->unsignedBigInteger('PaidBy')->nullable();
            $table->string('Notes', 255)->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->unique(['EmployeeID', 'Year'], 'ux_gratuity_employee_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRGratuities');
    }
};
