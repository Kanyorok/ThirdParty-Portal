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
        Schema::create('t_MedicalFunds', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('FundName');
            $table->bigInteger('ProviderId');
            $table->bigInteger('CoverageType');
            $table->decimal('CoverageLimit', 18)->nullable();
            $table->string('Description')->nullable();
            $table->boolean('IsActive')->default(true);
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_medica__3214ec078bbd980d');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_MedicalFunds');
    }
};
