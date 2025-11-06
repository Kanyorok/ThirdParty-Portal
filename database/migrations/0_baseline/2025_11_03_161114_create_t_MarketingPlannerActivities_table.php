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
        Schema::create('t_MarketingPlannerActivities', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('PlannerActivityID')->unique();
            $table->string('Name');
            $table->string('Location');
            $table->text('Notes')->nullable();
            $table->char('BranchId', 5)->index();
            $table->dateTime('StartOn');
            $table->dateTime('EndOn');
            $table->decimal('Budget', 14);
            $table->decimal('Actual', 14)->nullable();
            $table->bigInteger('PlannerId');
            $table->bigInteger('MasterPlannerId')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->text('Materials')->nullable();

            $table->primary(['Id'], 'pk__t_market__3214ec0778d381cf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_MarketingPlannerActivities');
    }
};
