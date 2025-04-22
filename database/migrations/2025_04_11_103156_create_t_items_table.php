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
        Schema::create('t_Items', function (Blueprint $table) {
            $table->id();
            $table->string('Name')->unique()->comment('Item name');
            $table->string('Description')->nullable()->comment('Item description');
            $table->enum('Type', ['good', 'service'])->default('good')->comment('Item type');

            //Fields for goods
            $table->unsignedInteger('CategoryId')->nullable()->comment('Category ID');
            $table->string('UOM')->nullable()->comment('Unit of measure');
            $table->decimal('UnitPrice', 10, 2)->default(0)->comment('Unit price');

            //Fields for services
            $table->text('ServiceScope')->nullable()->comment('Service scope');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            //Common fields
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Items');
    }
};
