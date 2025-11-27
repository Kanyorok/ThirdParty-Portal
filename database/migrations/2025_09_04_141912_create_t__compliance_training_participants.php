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
        Schema::create('t_ComplianceTrainingParticipants', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('TrainingID')->constrained('t_ComplianceTrainingSessions','Id')->onDelete('cascade');
            $table->unsignedBigInteger('UserID'); // from t_Users
            $table->boolean('Attended')->default(0);
            $table->timestamp('RegisteredOn')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
     Schema::dropIfExists('t_ComplianceTrainingParticipants');
    }
};
