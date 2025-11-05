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
        Schema::create('t_LegalCaseOutcomes', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('LegalCaseID');
            $table->string('Outcome');
            $table->date('JudgmentDate');
            $table->string('JudgeName');
            $table->text('CourtDecision');
            $table->decimal('PenaltyAmount', 15)->nullable();
            $table->text('Remarks')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_legalc__3214ec0745a646b2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_LegalCaseOutcomes');
    }
};
