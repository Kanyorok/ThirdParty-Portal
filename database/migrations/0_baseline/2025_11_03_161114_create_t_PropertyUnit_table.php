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
        Schema::create('t_PropertyUnit', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('PropertyID');
            $table->bigInteger('BlockID');
            $table->bigInteger('FloorID');
            $table->string('UnitCode');
            $table->integer('UnitSize');
            $table->boolean('IsRentable');
            $table->boolean('CurrentStatus');
            $table->string('Remarks');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_proper__3214ec07d4d9b21b');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_PropertyUnit');
    }
};
