<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HROvertimeRates', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('GradeID');
            $table->decimal('RateMultiplier', 8, 4)->default(1);
            $table->date('EffectiveFrom')->nullable();
            $table->date('EffectiveTo')->nullable();
            $table->boolean('IsActive')->default(true);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('GradeID')->references('Id')->on('t_HRJobGrades');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HROvertimeRates');
    }
};
