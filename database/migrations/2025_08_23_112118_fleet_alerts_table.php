<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_FleetAlertRules', function (Blueprint $table) {
            // Primary key (BIGINT IDENTITY)
            $table->bigIncrements('Id');

            // Core fields
            $table->string('Name', 150);
            $table->string('AlertType', 30);          // e.g., MILEAGE, DATE, SCHEDULE
            $table->text('Description')->nullable();

            // Triggers (nullable: only one may apply depending on AlertType)
            $table->integer('TriggerMileage')->nullable(); // km or miles per your convention
            $table->integer('TriggerDays')->nullable();    // days interval

            // Policy controls
            $table->string('Frequency', 30)->nullable();       // e.g., ONCE, DAILY, WEEKLY, MONTHLY
            $table->string('EscalationLevel', 30)->nullable(); // e.g., NONE, L1, L2, etc.

            // Status
            $table->boolean('IsActive')->default(true);

            // Audit
            $table->bigInteger('CreatedBy')->nullable();
            // Use SQL Server UTC default
            $table->dateTime('CreatedOn')->default(DB::raw('SYSUTCDATETIME()'));
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            // Helpful indexes
            $table->index('IsActive', 'IX_FleetAlertRules_IsActive');
            $table->index('AlertType', 'IX_FleetAlertRules_AlertType');
        });

        // Optional: add simple CHECKs for non-negative triggers on SQL Server
        // (These statements are safe no-ops on other DBs if you only run on SQL Server)
        try {
            DB::statement("
                ALTER TABLE t_FleetAlertRules
                ADD CONSTRAINT CK_FleetAlertRules_TriggerMileage
                CHECK (TriggerMileage IS NULL OR TriggerMileage >= 0)
            ");
            DB::statement("
                ALTER TABLE t_FleetAlertRules
                ADD CONSTRAINT CK_FleetAlertRules_TriggerDays
                CHECK (TriggerDays IS NULL OR TriggerDays >= 0)
            ");
        } catch (\Throwable $e) {
            // Ignore if the platform doesn't support it or constraints already exist
        }
    }

    public function down(): void
    {
        // Drop CHECK constraints first (SQL Server)
        try {
            DB::statement("ALTER TABLE t_FleetAlertRules DROP CONSTRAINT CK_FleetAlertRules_TriggerMileage");
        } catch (\Throwable $e) {
        }
        try {
            DB::statement("ALTER TABLE t_FleetAlertRules DROP CONSTRAINT CK_FleetAlertRules_TriggerDays");
        } catch (\Throwable $e) {
        }

        Schema::dropIfExists('t_FleetAlertRules');
    }
};
