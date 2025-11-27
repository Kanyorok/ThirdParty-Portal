<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_ModuleSources', function (Blueprint $table) {
            $table->id(); // This is your [Id] column
            $table->string('DocumentType', 100);
            $table->unsignedBigInteger('ModuleID');
            $table->foreign('ModuleID')->references('ModuleID')->on('t_Modules');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_ModuleSources');
    }
};
