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

        Schema::table('t_PrequalificationRoundSections', function (Blueprint $table) {
            if (!Schema::hasColumn('t_PrequalificationRoundSections', 'RoundID')) {
                $table->foreignId('RoundID')
                    ->after('Id')
                    ->constrained('t_PrequalificationRounds', 'RoundID')
                    ->onDelete('cascade');
            }
        });

        Schema::table('t_PrequalificationRoundCriteria', function (Blueprint $table) {
            if (!Schema::hasColumn('t_PrequalificationRoundCriteria', 'RoundID')) {
                $table->foreignId('RoundID')
                    ->after('Id')
                    ->constrained('t_PrequalificationRounds', 'RoundID')
                    ->onDelete('cascade');
            }
        });

        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            if (!Schema::hasColumn('t_SupplierPrequalificationApplications', 'RoundID')) {
                $table->foreignId('RoundID')
                    ->after('ApplicationID')
                    ->constrained('t_PrequalificationRounds', 'RoundID')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_PrequalificationRoundSections', function (Blueprint $table) {
            $table->dropForeign(['RoundID']);
            $table->dropColumn('RoundID');

            $table->foreignId('PeriodId')
                ->nullable()
                ->constrained('t_PrequalificationPeriod', 'Id')
                ->nullOnDelete();
        });

        Schema::table('t_PrequalificationRoundCriteria', function (Blueprint $table) {
            $table->dropForeign(['RoundID']);
            $table->dropColumn('RoundID');

            $table->foreignId('PeriodId')
                ->nullable()
                ->constrained('t_PrequalificationPeriod', 'Id')
                ->nullOnDelete();
        });

        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            $table->dropForeign(['RoundID']);
            $table->dropColumn('RoundID');

            $table->foreignId('PeriodId')
                ->nullable()
                ->constrained('t_PrequalificationPeriod', 'Id')
                ->nullOnDelete();
        });
    }
};
