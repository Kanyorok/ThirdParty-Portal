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
        Schema::table('t_SchedulePlan', function (Blueprint $table) {
            $table->string('ScheduleType', 50)->after('ScheduleQTY')->default('Quarterly');
            $table->string('Status', 50)->change();
            $table->foreignId('ModifiedBy')->nullable()->change();
            $table->dateTime('ModifiedOn')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_SchedulePlan', function (Blueprint $table) {
            $table->foreignId('ModifiedBy')->nullable(false)->change();
            $table->dateTime('ModifiedOn')->nullable(false)->change();
            $table->char('Status', 1)->change();
            $table->dropColumn('ScheduleType');
        });
    }
};
