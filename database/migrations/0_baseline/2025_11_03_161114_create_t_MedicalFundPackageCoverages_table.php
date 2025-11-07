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
        Schema::create('t_MedicalFundPackageCoverages', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('PackageId');
            $table->bigInteger('CoverageId');
            $table->decimal('AnnualLimit', 18)->nullable();
            $table->decimal('PerVisitLimit', 18)->nullable();
            $table->integer('WaitingPeriodDays')->nullable();
            $table->string('Scope', 20)->nullable();
            $table->boolean('IsActive')->default(true);
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_medica__3214ec07d1705aa2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_MedicalFundPackageCoverages');
    }
};
