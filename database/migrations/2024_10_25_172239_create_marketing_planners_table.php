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
        Schema::create('t_MarketingPlanner', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('PlannerID')->unique();
            $table->string('Name');
            $table->char('BranchId', '5')->nullable()->index();
            $table->char('Type', 2);
            $table->longText('Notes')->nullable();
            $table->dateTime('StartOn')->nullable();
            $table->dateTime('EndOn')->nullable();
            $table->foreignId('Modes')->nullable()->constrained('t_CRMCodeDetails', 'ID');
            $table->char('Status', 2);
            $table->foreignId('OwnerId')->constrained('t_Users', 'Id');
            $table->foreignId('MasterPlannerId')->nullable()->constrained('t_MarketingPlanner', 'Id');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->dateTime('ArchivedOn')->nullable();
            $table->foreignId('ArchivedBy')->nullable()->constrained('t_Users', 'Id');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_MarketingPlannerActivities', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('PlannerActivityID')->unique();
            $table->string('Name');
            $table->string('Location');
            $table->longText('Notes')->nullable();
            $table->char('BranchId', '5')->index();
            $table->dateTime('StartOn');
            $table->dateTime('EndOn');
            $table->decimal('Budget', 14);
            $table->decimal('Actual', 14)->nullable();
            $table->foreignId('PlannerId')->constrained('t_MarketingPlanner', 'Id');
            $table->foreignId('MasterPlannerId')->nullable()->constrained('t_MarketingPlanner', 'Id');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_MarketingPlannerActivityUsers', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('UserID')->constrained('t_Users', 'Id');
            $table->foreignId('ActivityId')->constrained('t_MarketingPlannerActivities', 'Id');
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
        Schema::dropIfExists('t_MarketingPlannerActivityUsers');
        Schema::dropIfExists('t_MarketingPlannerActivities');
        Schema::dropIfExists('t_MarketingPlanner');
    }
};
