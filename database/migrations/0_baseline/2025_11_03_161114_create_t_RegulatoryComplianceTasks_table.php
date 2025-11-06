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
        Schema::create('t_RegulatoryComplianceTasks', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('ObligationID');
            $table->string('TaskTitle');
            $table->text('TaskDescription')->nullable();
            $table->date('TaskDueDate')->nullable();
            $table->date('TaskCompletedDate')->nullable();
            $table->string('TaskStatus', 50)->default('Open');
            $table->string('ResponsibleOfficer', 150)->nullable();
            $table->string('EvidenceDocumentPath')->nullable();
            $table->timestamps();

            $table->primary(['Id'], 'pk__t_regula__3214ec0757742898');
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
