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
        Schema::create('t_BancassuranceClaimAssessments', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('ClaimId');
            $table->bigInteger('AssessedBy');
            $table->date('AssessmentDate');
            $table->decimal('AssessmentAmount', 10);
            $table->string('AssessmentComments');
            $table->bigInteger('Decision');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_bancas__3214ec07ce447ab9');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BancassuranceClaimAssessments');
    }
};
