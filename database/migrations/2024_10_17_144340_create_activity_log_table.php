<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogTable extends Migration
{
    public function up(): void
    {
        Schema::connection(config('activitylog.database_connection'))->create(config('activitylog.table_name'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->string("subject_type")->nullable();
            $table->string("subject_id", 100)->nullable();
            $table->string("causer_type")->nullable();
            $table->string("causer_id", 100)->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->index('log_name');

            $table->index(["subject_type", "subject_id"], 'subject');
            $table->index(["causer_type", "causer_id"], 'causer');
        });
    }

    public function down(): void
    {
        Schema::connection(config('activitylog.database_connection'))->dropIfExists(config('activitylog.table_name'));
    }
}
