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
        Schema::create('t_SYSJobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->text('payload');
            $table->tinyInteger('attempts');
            $table->integer('reserved_at')->nullable();
            $table->integer('available_at');
            $table->integer('created_at');

            $table->primary(['id'], 'pk__t_sysjob__3213e83f7d92f911');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_SYSJobs');
    }
};
