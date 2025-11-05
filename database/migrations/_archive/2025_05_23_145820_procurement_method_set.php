<?php

use Composer\Semver\Constraint\Constraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_ProcurementMethod', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('MethodId')->unique();
            $table->foreignId('ApprovedPlanId')->constrained('t_ConsolidatedProcurementPlan', 'PlanID');
            $table->foreignId('ApprovedPlanLineId')->constrained('t_PlanLineItem', 'LineItemID');
            $table->string('AssignedMethod');
            $table->text('Justification');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ProcurementMethod');
    }
};
