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
        // --- Customers ---
        Schema::table('t_BancassuranceCustomers', function (Blueprint $table) {
            if (Schema::hasColumn('t_BancassuranceCustomers', 'ReferralID')) {
                $table->dropForeign(['ReferralID']);
                $table->dropColumn('ReferralID');
            }
        });

        // --- Policies ---
        Schema::table('t_BancassurancePolicies', function (Blueprint $table) {
            if (Schema::hasColumn('t_BancassurancePolicies', 'ReferralID')) {
                $table->dropForeign(['ReferralID']);
                $table->dropColumn('ReferralID');
            }
        });

        // Recreate Customers.ReferralID as nullable FK
        Schema::table('t_BancassuranceCustomers', function (Blueprint $table) {
            if (!Schema::hasColumn('t_BancassuranceCustomers', 'ReferralID')) {
                $table->foreignId('ReferralID')->nullable()
                    ->constrained('t_BancassuranceReferrals', 'Id');
            }
        });

        // Recreate Policies.ReferralID & RiderAddOnId as nullable FKs
        Schema::table('t_BancassurancePolicies', function (Blueprint $table) {
            if (!Schema::hasColumn('t_BancassurancePolicies', 'ReferralID')) {
                $table->foreignId('ReferralID')->nullable()->constrained('t_BancassuranceReferrals', 'Id');
            }

            if (!Schema::hasColumn('t_BancassurancePolicies', 'RiderAddOnId')) {
                $table->foreignId('RiderAddOnId')->nullable()->constrained('t_InsuranceProductRiders', 'Id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BancassuranceCustomers', function (Blueprint $table) {
            if (Schema::hasColumn('t_BancassuranceCustomers', 'ReferralID')) {
                $table->dropForeign(['ReferralID']);
                $table->dropColumn('ReferralID');
            }
        });

        Schema::table('t_BancassurancePolicies', function (Blueprint $table) {
            if (Schema::hasColumn('t_BancassurancePolicies', 'ReferralID')) {
                $table->dropForeign(['ReferralID']);
                $table->dropColumn('ReferralID');
            }

            if (Schema::hasColumn('t_BancassurancePolicies', 'RiderAddOnId')) {
                $table->dropForeign(['RiderAddOnId']);
                $table->dropColumn('RiderAddOnId');
            }
        });

        // Recreate original ReferralID columns (nullable to avoid errors)
        Schema::table('t_BancassuranceCustomers', function (Blueprint $table) {
            if (!Schema::hasColumn('t_BancassuranceCustomers', 'ReferralID')) {
                $table->foreignId('ReferralID')
                    ->nullable()
                    ->constrained('t_BancassuranceReferrals', 'Id');
            }
        });

        Schema::table('t_BancassurancePolicies', function (Blueprint $table) {
            if (!Schema::hasColumn('t_BancassurancePolicies', 'ReferralID')) {
                $table->foreignId('ReferralID')
                    ->nullable()
                    ->constrained('t_BancassuranceReferrals', 'Id');
            }
        });
    }

};
