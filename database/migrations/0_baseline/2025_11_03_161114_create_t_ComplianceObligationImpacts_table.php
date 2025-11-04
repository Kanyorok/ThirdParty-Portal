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
            $table->bigIncrements('Id');
            $table->bigInteger('ObligationID');
            $table->text('ImpactDescription');
            $table->string('Department', 150)->nullable();
            $table->bigInteger('AssessedBy');
            $table->dateTime('AssessedOn')->useCurrent();

            $table->primary(['Id'], 'pk__t_compli__3214ec0714f30d62');
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
