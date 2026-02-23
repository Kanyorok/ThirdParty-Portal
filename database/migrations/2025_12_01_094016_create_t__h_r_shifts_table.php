<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRShifts', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Code', 50)->unique();
            $table->string('Name', 150);
            $table->time('StartTime');
            $table->time('EndTime');
            $table->boolean('IsOvernight')->default(0);
            $table->integer('GraceMinutes')->default(0); // allowed late minutes

            $table->boolean('IsActive')->default(1);

            $table->unsignedBigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRShifts');
    }
};
