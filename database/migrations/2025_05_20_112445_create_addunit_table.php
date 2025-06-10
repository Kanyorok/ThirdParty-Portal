<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_PropertyUnit', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('PropertyID')->constrained('t_PropertyRegistry', 'Id');
            $table->foreignId('BlockID')->constrained('t_PropertyBlock', 'Id');
            $table->foreignId('FloorID')->constrained('t_PropertyFloor', 'Id');
            $table->string('UnitCode');
            $table->integer('UnitSize');
            $table->boolean('IsRentable');
            $table->boolean('CurrentStatus');
            $table->string('Remarks');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_AddUnit');
    }
};
