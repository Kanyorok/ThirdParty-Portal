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
            $table->bigIncrements('Id');
            $table->bigInteger('ControlID');
            $table->bigInteger('ObligationID');

            $table->primary(['Id'], 'pk__t_compli__3214ec07b8826a46');
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
