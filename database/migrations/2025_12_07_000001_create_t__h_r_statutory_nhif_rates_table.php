<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRStatutoryNHIFRates', function (Blueprint $table) {
            $table->id('Id');
            $table->string('BandName', 100);
            $table->decimal('IncomeFrom', 18, 2)->default(0);
            $table->decimal('IncomeTo', 18, 2)->nullable();
            $table->decimal('EmployeeRate', 9, 4)->default(0); // percentage or amount
            $table->decimal('EmployerRate', 9, 4)->nullable();
            $table->boolean('IsPercentage')->default(true);
            $table->date('EffectiveFrom');
            $table->date('EffectiveTo')->nullable();
            $table->boolean('IsActive')->default(true);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRStatutoryNHIFRates');
    }
};
