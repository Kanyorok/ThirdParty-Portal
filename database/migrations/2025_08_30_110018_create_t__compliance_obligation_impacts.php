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
    Schema::create('t_ComplianceObligationImpacts', function (Blueprint $table) {
        $table->id('Id');
        $table->foreignId('ObligationID')->constrained('t_ComplianceObligations', 'Id')->onDelete('cascade');
        $table->text('ImpactDescription');
        $table->string('Department', 150)->nullable();
        $table->unsignedBigInteger('AssessedBy');
        $table->timestamp('AssessedOn')->useCurrent();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
     Schema::dropIfExists('t_ComplianceObligationImpacts');
    }
};
