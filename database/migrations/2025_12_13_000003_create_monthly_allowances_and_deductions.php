<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_HRMonthlyAllowances', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('EmployeeID');
            $table->string('Name', 150);
            $table->decimal('Amount', 18, 2);
            $table->unsignedTinyInteger('Month');
            $table->unsignedSmallInteger('Year');
            $table->boolean('IsTaxable')->default(true);
            $table->string('Status', 20)->default('Pending'); // Pending, Approved, Rejected
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
        });

        Schema::create('t_HRMonthlyDeductions', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('EmployeeID');
            $table->string('Name', 150);
            $table->decimal('Amount', 18, 2);
            $table->unsignedTinyInteger('Month');
            $table->unsignedSmallInteger('Year');
            $table->string('Status', 20)->default('Pending'); // Pending, Approved, Rejected
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRMonthlyDeductions');
        Schema::dropIfExists('t_HRMonthlyAllowances');
    }
};
