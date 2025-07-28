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
        // === DROP columns on t_MaintenanceRequest if they exist ===
        $invoiceColumnsToDrop = ['IssueType', 'Priority'];
        foreach ($invoiceColumnsToDrop as $col) {
            if (Schema::hasColumn('t_MaintenanceRequest', $col)) {
                Schema::table('t_MaintenanceRequest', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }

        // === RE-ADD columns to t_RentInvoice ===
        Schema::table('t_MaintenanceRequest', function (Blueprint $table) {
            if (!Schema::hasColumn('t_MaintenanceRequest', 'IssueType')) {
                $table->foreignId('IssueType')->constrained('t_CodeDetails','ID')->nullable();
            }
            if (!Schema::hasColumn('t_MaintenanceRequest', 'Priority')) {
                $table->foreignId('Priority')->constrained('t_CodeDetails','ID')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_MaintenanceRequest', function (Blueprint $table) {
            // First drop the foreign keys if they exist
            if (Schema::hasColumn('t_MaintenanceRequest', 'IssueType')) {
                $table->dropForeign(['IssueType']);
                $table->dropColumn('IssueType');
            }

            if (Schema::hasColumn('t_MaintenanceRequest', 'Priority')) {
                $table->dropForeign(['Priority']);
                $table->dropColumn('Priority');
            }
        });
    }
};
