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
        Schema::create('t_PropertyRegistry', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('PropertyName');
            $table->string('PropertyCode');
            $table->bigInteger('PropertyType');
            $table->bigInteger('Category');
            $table->string('Owner');
            $table->date('AcquisitionDate');
            $table->string('Address');
            $table->string('PropertyDescription');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->boolean('IsActive')->default(true);
            $table->bigInteger('CountryId')->nullable();
            $table->bigInteger('LocationId')->nullable();

            $table->primary(['Id'], 'pk__t_proper__3214ec07f01baefe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_PropertyRegistry');
    }
};
