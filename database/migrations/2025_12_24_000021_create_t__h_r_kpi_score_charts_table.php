<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRKPIScoreCharts', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->unsignedBigInteger('RatingScaleID')->nullable();
            $table->decimal('MinPercent', 6, 2);
            $table->decimal('MaxPercent', 6, 2)->nullable();
            $table->decimal('RatingValue', 6, 2);
            $table->string('RatingLabel', 150);
            $table->boolean('IsActive')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRKPIScoreCharts');
    }
};
