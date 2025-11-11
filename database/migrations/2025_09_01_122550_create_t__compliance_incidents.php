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
        Schema::create('t_ComplianceIncidents', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('ObligationID')->nullable()->constrained('t_ComplianceObligations', 'Id')->nullOnDelete();
            $table->string('Title', 255);
            $table->text('Description')->nullable();
            $table->date('IncidentDate');
            $table->foreignId('SeverityID')->constrained('t_IncidentSeverityLevels', 'Id');
            $table->unsignedBigInteger('ResponsibleUserID')->nullable(); // from t_Users
            $table->string('Status', 50)->default('Open'); // Open, Investigating, Resolved, Escalated

            $table->unsignedBigInteger('CreatedBy');
            $table->timestamp('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->timestamp('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->timestamp('DeletedOn')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ComplianceIncidents');
    }
};
