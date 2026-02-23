<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRJobGrades', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Code', 50)->unique();
            $table->string('Name', 150);
            $table->decimal('MinSalary', 18, 2)->nullable();
            $table->decimal('MaxSalary', 18, 2)->nullable();
            $table->string('Description', 255)->nullable();
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
        Schema::dropIfExists('t_HRJobGrades');
    }
};