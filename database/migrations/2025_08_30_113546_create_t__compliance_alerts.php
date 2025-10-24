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
        Schema::create('t_ComplianceAlerts', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('CalendarEntryID')->constrained('t_ComplianceCalendarEntries', 'Id')->onDelete('cascade');
            $table->integer('DaysBefore')->default(0); // e.g., 7 days before
            $table->string('EscalationLevel', 100)->nullable(); // e.g., Compliance Officer, Head of Risk
            $table->string('Channel', 50)->default('Email'); // Email, SMS, Dashboard, Teams
            $table->boolean('IsActive')->default(1);

            $table->unsignedBigInteger('CreatedBy');
            $table->timestamp('CreatedOn')->useCurrent();
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
