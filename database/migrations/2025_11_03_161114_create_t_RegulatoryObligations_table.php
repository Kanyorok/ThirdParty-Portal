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
            $table->bigIncrements('Id');
            $table->string('ObligationTitle');
            $table->text('ObligationDescription')->nullable();
            $table->string('RegulatoryBody', 150)->nullable();
            $table->string('ObligationType', 100)->nullable();
            $table->date('EffectiveDate')->nullable();
            $table->date('DueDate')->nullable();
            $table->boolean('IsRecurring')->default(false);
            $table->string('RecurrenceType', 50)->nullable();
            $table->string('Status', 50)->default('Pending');
            $table->string('ComplianceArea', 100)->nullable();
            $table->string('AttachmentPath')->nullable();
            $table->timestamps();

            $table->primary(['Id'], 'pk__t_regula__3214ec07e48c98ec');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_RegulatoryObligations');
    }
};
