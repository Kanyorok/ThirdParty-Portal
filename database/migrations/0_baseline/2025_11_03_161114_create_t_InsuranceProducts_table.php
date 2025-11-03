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
        Schema::create('t_InsuranceProducts', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('InsuranceProviderID');
            $table->string('Name');
            $table->string('Description');
            $table->boolean('IsActive');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('Type')->default(1);

            $table->primary(['Id'], 'pk__t_insura__3214ec07a1dfc8e6');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_InsuranceProducts');
    }
};
