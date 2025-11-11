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
        Schema::create('t_GPSCoordinates', static function (Blueprint $table) {
            $table->id('Id');
            $table->string("Source");
            $table->string("SourceID", 100);
            $table->decimal('Latitude', 13, 10);
            $table->decimal('Longitude', 13, 10);
            $table->json('Extra')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');

            $table->index(["Latitude", "Longitude"]);
            $table->index(["Source", "SourceID"]);
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
