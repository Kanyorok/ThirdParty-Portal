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
        Schema::create('sysdiagrams', function (Blueprint $table) {
            $table->string('name');
            $table->integer('principal_id');
            $table->increments('diagram_id');
            $table->integer('version')->nullable();
            $table->binary('definition')->nullable();

            $table->unique(['principal_id', 'name'], 'uk_principal_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sysdiagrams');
    }
};
