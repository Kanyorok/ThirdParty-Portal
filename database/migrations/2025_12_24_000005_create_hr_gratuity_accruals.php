<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_HRGratuityAccruals', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->unsignedBigInteger('PayrollRunID');
            $table->unsignedBigInteger('EmployeeID');
            $table->integer('Year');
            $table->integer('Month');
            $table->decimal('RatePercent', 10, 4)->default(0);
            $table->string('CalcBasis', 20)->nullable();
            $table->decimal('BaseAmount', 18, 2)->default(0);
            $table->decimal('Amount', 18, 2)->default(0);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->unique(['PayrollRunID', 'EmployeeID'], 'ux_gratuity_accrual_run');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRGratuityAccruals');
    }
};
