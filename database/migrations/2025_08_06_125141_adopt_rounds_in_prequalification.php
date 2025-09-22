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
        // Drop old PeriodId columns and their foreign keys if they exist
        Schema::table('t_PrequalificationRoundSections', function (Blueprint $table) {
            if (Schema::hasColumn('t_PrequalificationRoundSections', 'PeriodId')) {
                $table->dropForeign(['PeriodId']);
                $table->dropColumn('PeriodId');
            }
        });

        Schema::table('t_PrequalificationRoundCriteria', function (Blueprint $table) {
            if (Schema::hasColumn('t_PrequalificationRoundCriteria', 'PeriodId')) {
                $table->dropForeign(['PeriodId']);
                $table->dropColumn('PeriodId');
            }
        });

        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            if (Schema::hasColumn('t_SupplierPrequalificationApplications', 'PeriodId')) {
                $table->dropForeign(['PeriodId']);
                $table->dropColumn('PeriodId');
            }
        });

        // Add new RoundId columns with proper foreign key constraints
        Schema::table('t_PrequalificationRoundSections', function (Blueprint $table) {
            if (!Schema::hasColumn('t_PrequalificationRoundSections', 'RoundId')) {
                $table->unsignedBigInteger('RoundId')->after('Id');
                $table->foreign('RoundId')
                    ->references('RoundID')
                    ->on('t_PrequalificationRounds')
                    ->onDelete('cascade');
            }
        });

        Schema::table('t_PrequalificationRoundCriteria', function (Blueprint $table) {
            if (!Schema::hasColumn('t_PrequalificationRoundCriteria', 'RoundId')) {
                $table->unsignedBigInteger('RoundId')->after('Id');
                $table->foreign('RoundId')
                    ->references('RoundID')
                    ->on('t_PrequalificationRounds')
                    ->onDelete('cascade');
            }
        });

        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            if (!Schema::hasColumn('t_SupplierPrequalificationApplications', 'RoundId')) {
                $table->unsignedBigInteger('RoundId')->after('ApplicationID');
                $table->foreign('RoundId')
                    ->references('RoundID')
                    ->on('t_PrequalificationRounds')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Simply drop the RoundID columns and their foreign keys
        // No need to restore Period references since we've moved away from that concept

        Schema::table('t_PrequalificationRoundSections', function (Blueprint $table) {
            if (Schema::hasColumn('t_PrequalificationRoundSections', 'RoundID')) {
                $table->dropForeign(['RoundID']);
                $table->dropColumn('RoundID');
            }
        });

        Schema::table('t_PrequalificationRoundCriteria', function (Blueprint $table) {
            if (Schema::hasColumn('t_PrequalificationRoundCriteria', 'RoundID')) {
                $table->dropForeign(['RoundID']);
                $table->dropColumn('RoundID');
            }
        });

        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            if (Schema::hasColumn('t_SupplierPrequalificationApplications', 'RoundID')) {
                $table->dropForeign(['RoundID']);
                $table->dropColumn('RoundID');
            }
        });
    }
};
