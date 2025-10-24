<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_RegulatoryComplianceTasks', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('ObligationID')->constrained('t_RegulatoryObligations', 'Id')->onDelete('cascade');
            $table->string('TaskTitle', 255);
            $table->text('TaskDescription')->nullable();
            $table->date('TaskDueDate')->nullable();
            $table->date('TaskCompletedDate')->nullable();
            $table->string('TaskStatus', 50)->default('Open'); // Open, In Progress, Completed
            $table->string('ResponsibleOfficer', 150)->nullable();
            $table->string('EvidenceDocumentPath')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_RegulatoryComplianceTasks');
    }
};
