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
        Schema::create('t_SYSActivityLog', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable()->index();
            $table->text('description');
            $table->string('subject_type')->nullable();
            $table->string('subject_id', 100)->nullable();
            $table->string('causer_type')->nullable();
            $table->string('causer_id', 100)->nullable();
            $table->text('properties')->nullable();
            $table->timestamps();
            $table->string('event')->nullable();
            $table->uuid('batch_uuid')->nullable();

            $table->index(['causer_type', 'causer_id'], 'causer');
            $table->primary(['id'], 'pk__t_sysact__3213e83f911c4624');
            $table->index(['subject_type', 'subject_id'], 'subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_SYSActivityLog');
    }
};
