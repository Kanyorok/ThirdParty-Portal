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
    Schema::create('t_RegulatoryObligations', function (Blueprint $table) {
        $table->id('Id');
        $table->string('ObligationTitle', 255);
        $table->text('ObligationDescription')->nullable();
        $table->string('RegulatoryBody', 150)->nullable(); // e.g., CBK, SASRA, KRA
        $table->string('ObligationType', 100)->nullable(); // e.g., Filing, Licensing
        $table->date('EffectiveDate')->nullable();
        $table->date('DueDate')->nullable();
        $table->boolean('IsRecurring')->default(false);
        $table->string('RecurrenceType', 50)->nullable(); // Monthly, Quarterly, Yearly
        $table->string('Status', 50)->default('Pending'); // Pending, In Progress, Completed, Lapsed
        $table->string('ComplianceArea', 100)->nullable(); // AML, Tax, Licensing, Audit
        $table->string('AttachmentPath')->nullable();
        $table->timestamps(); // Created_at, updated_at
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop CHECK constraints first (SQL Server)
  
        Schema::dropIfExists('t_RegulatoryObligations');
    }
    
};
