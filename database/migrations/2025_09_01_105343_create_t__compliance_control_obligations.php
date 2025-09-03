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
        Schema::create('t_ComplianceControlObligations', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('ControlID')->constrained('t_ComplianceControls', 'Id')->onDelete('cascade');
            $table->foreignId('ObligationID')->constrained('t_ComplianceObligations', 'Id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
     Schema::dropIfExists('t_ComplianceControlObligations');
    }
};
