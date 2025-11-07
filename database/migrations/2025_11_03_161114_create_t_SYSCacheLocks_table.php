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
        Schema::create('t_SYSCacheLocks', function (Blueprint $table) {
            $table->string('key');
            $table->string('owner');
            $table->integer('expiration');

            $table->primary(['key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_SYSCacheLocks');
    }
};
