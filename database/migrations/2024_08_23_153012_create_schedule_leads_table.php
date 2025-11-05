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
        Schema::create('t_ScheduleLeads', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('LeadId')->constrained('t_Leads', 'LeadID')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('ScheduleId')->constrained('t_Schedule', 'ScheduleID')->cascadeOnDelete()->cascadeOnDelete();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ScheduleLeads');
    }
};
