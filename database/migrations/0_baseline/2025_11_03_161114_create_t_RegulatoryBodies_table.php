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
        Schema::create('t_RegulatoryBodies', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Name', 150);
            $table->string('Jurisdiction', 150)->nullable();
            $table->string('ContactPerson', 150)->nullable();
            $table->string('ContactEmail', 150)->nullable();
            $table->string('ContactPhone', 50)->nullable();
            $table->boolean('IsActive')->default(true);
            $table->timestamps();

            $table->primary(['Id'], 'pk__t_regula__3214ec07b519745d');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_RegulatoryBodies');
    }
};
