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
        Schema::create('t_ComplianceAlerts', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('CalendarEntryID');
            $table->integer('DaysBefore')->default(0);
            $table->string('EscalationLevel', 100)->nullable();
            $table->string('Channel', 50)->default('Email');
            $table->boolean('IsActive')->default(true);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();

            $table->primary(['Id'], 'pk__t_compli__3214ec07b00d225d');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ComplianceAlerts');
    }
};
