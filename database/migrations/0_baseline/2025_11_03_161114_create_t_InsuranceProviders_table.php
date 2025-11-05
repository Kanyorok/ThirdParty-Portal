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
        Schema::create('t_InsuranceProviders', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('InsuranceProviderNO');
            $table->string('Name');
            $table->string('ContactPerson');
            $table->string('Email')->unique();
            $table->string('Phone')->unique();
            $table->boolean('IsActive');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('Country')->default(1);

            $table->primary(['Id'], 'pk__t_insura__3214ec074989304f');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_InsuranceProviders');
    }
};
