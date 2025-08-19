<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_LegalCaseOutcomes', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('LegalCaseID')->constrained('t_LegalCases', 'Id');
            $table->string('Outcome');                        // required
            $table->date('JudgmentDate');                     // required
            $table->string('JudgeName');          // optional
            $table->text('CourtDecision');        // optional
            $table->decimal('PenaltyAmount', 15, 2)->nullable(); // optional, large enough for fines
            $table->text('Remarks')->nullable();

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_LegalCaseOutcomes');
    }
};
