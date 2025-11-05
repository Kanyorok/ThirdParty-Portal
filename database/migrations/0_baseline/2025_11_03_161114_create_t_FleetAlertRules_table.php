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
        Schema::create('t_FleetAlertRules', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Name', 150);
            $table->string('AlertType', 30)->index('ix_fleetalertrules_alerttype');
            $table->text('Description')->nullable();
            $table->integer('TriggerMileage')->nullable();
            $table->integer('TriggerDays')->nullable();
            $table->string('Frequency', 30)->nullable();
            $table->string('EscalationLevel', 30)->nullable();
            $table->boolean('IsActive')->default(true)->index('ix_fleetalertrules_isactive');
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->default('sysutcdatetime()');
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->primary(['Id'], 'pk__t_fleeta__3214ec070cd69d30');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FleetAlertRules');
    }
};
