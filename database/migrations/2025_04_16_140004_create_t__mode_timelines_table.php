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
        Schema::create('t_ModeTimelines', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('ProcurementModeId')->constrained('t_ProcurementModes')->onDelete('cascade');
            $table->string('Stage'); // E.g. Approval, Evaluation
            $table->integer('DurationDays'); // E.g. 7, 14, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ModeTimelines');
    }
};
