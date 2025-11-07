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
        Schema::create('t_SYSPasswordResetTokens', function (Blueprint $table) {
            $table->string('email');
            $table->string('token');
            $table->dateTime('created_at')->nullable();

            $table->primary(['email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_SYSPasswordResetTokens');
    }
};
