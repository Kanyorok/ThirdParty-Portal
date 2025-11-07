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
        Schema::create('t_CompliancePolicyAcknowledgments', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('PolicyID');
            $table->bigInteger('UserID');
            $table->dateTime('AcknowledgedOn')->useCurrent();

            $table->primary(['Id'], 'pk__t_compli__3214ec07c1164343');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_CompliancePolicyAcknowledgments');
    }
};
