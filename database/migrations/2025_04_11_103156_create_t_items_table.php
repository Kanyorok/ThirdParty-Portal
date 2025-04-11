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
        Schema::create('t_items', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Item name');
            $table->string('description')->nullable()->comment('Item description');
            $table->enum('type', ['good', 'service'])->default('good')->comment('Item type');

            //Fields for goods
            $table->unsignedInteger('category_id')->nullable()->comment('Category ID');
            $table->string('unit_of_measure')->nullable()->comment('Unit of measure');
            $table->decimal('unit_price', 10, 2)->default(0)->comment('Unit price');

            //Fields for services
            $table->text('service_scope')->nullable()->comment('Service scope');

            //Common fields
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_items');
    }
};
