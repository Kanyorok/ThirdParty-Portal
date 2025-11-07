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
        Schema::create('t_MarketingPlanner', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('PlannerID')->unique();
            $table->string('Name');
            $table->char('BranchId', 5)->nullable()->index();
            $table->char('Type', 2);
            $table->text('Notes')->nullable();
            $table->dateTime('StartOn')->nullable();
            $table->dateTime('EndOn')->nullable();
            $table->bigInteger('Modes')->nullable();
            $table->char('Status', 2);
            $table->bigInteger('OwnerId');
            $table->bigInteger('MasterPlannerId')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->dateTime('ArchivedOn')->nullable();
            $table->bigInteger('ArchivedBy')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_market__3214ec0732f575fa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_MarketingPlanner');
    }
};
