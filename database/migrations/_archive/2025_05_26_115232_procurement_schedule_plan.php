<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_SchedulePlan', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('ScheduleId')->unique();
            $table->foreignId('PlanId')->constrained('t_ConsolidatedProcurementPlan', 'PlanID');
            $table->foreignId('PlanLineId')->constrained('t_PlanLineItem', 'LineItemID');
            $table->integer('ScheduleQTY');
            $table->char('Status', 1);
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
        Schema::dropIfExists('t_SchedulePlan');
    }
};
