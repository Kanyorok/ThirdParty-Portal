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
        Schema::create('t_Competitors', static function (Blueprint $table) {
            $table->id('CompetitorID');
            $table->string('CompetitorName');
            $table->foreignId('LocationID')->nullable()->constrained('t_Localities', 'ID');
            $table->foreignId('Logo')->nullable()->constrained('t_CRMImages', 'ImageID');
            $table->string('Email')->nullable();
            $table->string('Website')->nullable();
            $table->string('Phone')->nullable();
            $table->string('CoreBusiness')->nullable();
            $table->unsignedBigInteger('Clients')->nullable();
            $table->string('MarketShare')->nullable();
            $table->longText('Notes')->nullable();
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
        Schema::dropIfExists('t_Competitors');
    }
};
