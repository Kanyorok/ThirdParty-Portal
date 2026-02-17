<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRKPIRatingScales', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Name', 150);
            $table->string('Code', 50)->unique();
            $table->decimal('MinScore', 8, 2)->default(0);
            $table->decimal('MaxScore', 8, 2)->default(5);
            $table->string('Description', 255)->nullable();
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
        Schema::dropIfExists('t_HRKPIRatingScales');
    }
};
