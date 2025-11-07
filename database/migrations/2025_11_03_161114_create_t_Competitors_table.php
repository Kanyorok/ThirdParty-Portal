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
        Schema::create('t_Competitors', function (Blueprint $table) {
            $table->bigIncrements('CompetitorID');
            $table->string('CompetitorName');
            $table->bigInteger('LocationID')->nullable();
            $table->bigInteger('Logo')->nullable();
            $table->string('Email')->nullable();
            $table->string('Website')->nullable();
            $table->string('Phone')->nullable();
            $table->string('CoreBusiness')->nullable();
            $table->bigInteger('Clients')->nullable();
            $table->string('MarketShare')->nullable();
            $table->text('Notes')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->text('Processing')->nullable();
            $table->bigInteger('CountryId')->nullable();

            $table->primary(['CompetitorID'], 'pk__t_compet__626f45d04d5e6aa9');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Competitors');
    }
};
