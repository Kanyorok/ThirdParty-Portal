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
        Schema::create('t_ContractedDrivers', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('DriverNo')->unique();
            $table->string('FullName');
            $table->string('NationalID')->nullable();
            $table->string('Phone')->nullable();
            $table->date('ContractStartDate')->nullable();
            $table->date('ContractEndDate')->nullable();
            $table->string('Notes')->nullable();
            $table->boolean('IsActive')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('Company')->nullable();

            $table->primary(['Id'], 'pk__t_contra__3214ec078cce67e1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ContractedDrivers');
    }
};
