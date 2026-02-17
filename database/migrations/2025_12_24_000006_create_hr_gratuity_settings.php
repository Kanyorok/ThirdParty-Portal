<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_HRGratuitySettings', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('ApplyFor', 50)->nullable();
            $table->decimal('RatePercent', 10, 4)->default(0);
            $table->string('CalcBasis', 20)->default('Basic');
            $table->date('EffectiveFrom')->nullable();
            $table->date('EffectiveTo')->nullable();
            $table->boolean('IsActive')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRGratuitySettings');
    }
};
