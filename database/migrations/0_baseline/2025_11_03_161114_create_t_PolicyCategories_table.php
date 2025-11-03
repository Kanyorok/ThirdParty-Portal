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
        Schema::create('t_PolicyCategories', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Name', 150);
            $table->string('Description')->nullable();
            $table->boolean('IsActive')->default(true);
            $table->timestamps();

            $table->primary(['Id'], 'pk__t_policy__3214ec07988d0b21');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_PolicyCategories');
    }
};
