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
        Schema::create('t_Localities', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Name');
            $table->string('LocationType', 100);
            $table->boolean('IsActive')->default(true);
            $table->bigInteger('LocalityID')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('CountryId')->nullable();

            $table->primary(['ID'], 'pk__t_locali__3214ec27bbf9d2f4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Localities');
    }
};
