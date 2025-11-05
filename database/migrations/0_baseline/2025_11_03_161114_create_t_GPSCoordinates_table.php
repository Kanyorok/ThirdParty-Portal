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
        Schema::create('t_GPSCoordinates', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Source');
            $table->string('SourceID', 100);
            $table->decimal('Latitude', 13, 10);
            $table->decimal('Longitude', 13, 10);
            $table->text('Extra')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');

            $table->primary(['Id'], 'pk__t_gpscoo__3214ec074bbe391b');
            $table->index(['Latitude', 'Longitude']);
            $table->index(['Source', 'SourceID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_GPSCoordinates');
    }
};
