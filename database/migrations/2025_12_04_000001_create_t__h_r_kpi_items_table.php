<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRKPIItems', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Code', 50)->unique();
            $table->string('Name', 200);
            $table->string('Category', 100)->nullable();
            $table->string('Unit', 50)->nullable();
            $table->decimal('DefaultWeight', 8, 2)->nullable();
            $table->string('Description', 500)->nullable();
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
        Schema::dropIfExists('t_HRKPIItems');
    }
};
