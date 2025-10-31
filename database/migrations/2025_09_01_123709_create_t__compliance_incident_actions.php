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
        Schema::create('t_ComplianceIncidentActions', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('IncidentID')->constrained('t_ComplianceIncidents', 'Id')->onDelete('cascade');
            $table->text('RootCause')->nullable();
            $table->text('CorrectiveAction')->nullable();
            $table->unsignedBigInteger('ActionOwnerID')->nullable(); // from t_Users
            $table->date('DueDate')->nullable();
            $table->string('Status', 50)->default('Pending'); // Pending, Completed

            $table->unsignedBigInteger('CreatedBy');
            $table->timestamp('CreatedOn')->useCurrent();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ComplianceIncidentActions');
    }
};
