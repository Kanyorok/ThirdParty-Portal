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
    Schema::table('t_BancassuranceCustomers', function (Blueprint $table) {
        $table->dropForeign(['ReferralID']);
        $table->dropColumn('ReferralID');
    });

        Schema::table('t_BancassurancePolicies', function (Blueprint $table) {
        // Drop the foreign key constraint first
        $table->dropForeign(['ReferralID']);
        // Drop the column
        $table->dropColumn('ReferralID');
    });

    Schema::table('t_BancassuranceCustomers', function (Blueprint $table) {
        // Recreate as nullable with foreign key
        $table->foreignId('ReferralID')->nullable()->constrained('t_BancassuranceReferrals', 'Id');
    });

    Schema::table('t_BancassurancePolicies', function (Blueprint $table) {
        // Recreate as nullable with foreign key
        $table->foreignId('ReferralID')->nullable()->constrained('t_BancassuranceReferrals', 'Id');
    });
}

public function down(): void
{
    Schema::table('t_BancassuranceCustomers', function (Blueprint $table) {
        $table->dropForeign(['ReferralID']);
        $table->dropColumn('ReferralID');
    });

    Schema::table('t_BancassurancePolicies', function (Blueprint $table) {
        $table->dropForeign(['ReferralID']);
        $table->dropColumn('ReferralID');
    });

    Schema::table('t_BancassuranceCustomers', function (Blueprint $table) {
        // Add as nullable to avoid SQL Server error
        $table->foreignId('ReferralID')->nullable()->constrained('t_BancassuranceReferrals', 'Id');
    });

    Schema::table('t_BancassurancePolicies', function (Blueprint $table) {
        // Add as nullable to avoid SQL Server error
        $table->foreignId('ReferralID')->nullable()->constrained('t_BancassuranceReferrals', 'Id');
    });
}
};
