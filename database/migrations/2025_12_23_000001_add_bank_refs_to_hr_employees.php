<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HREmployees', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HREmployees', 'BankID')) {
                $table->unsignedBigInteger('BankID')->nullable()->after('PaymentMode');
            }
            if (!Schema::hasColumn('t_HREmployees', 'BankBranchID')) {
                $table->unsignedBigInteger('BankBranchID')->nullable()->after('BankID');
            }
        });

        // Backfill BankID using BankName match
        DB::statement("
            UPDATE e
            SET e.BankID = b.BankID
            FROM t_HREmployees e
            INNER JOIN t_Banks b ON b.BankName = e.BankName
            WHERE e.BankID IS NULL AND e.BankName IS NOT NULL
        ");

        // Backfill BankBranchID using BranchName + BankID match
        DB::statement("
            UPDATE e
            SET e.BankBranchID = br.BranchID
            FROM t_HREmployees e
            INNER JOIN t_BankBranches br
                ON br.BranchName = e.BankBranch
                AND br.BankID = e.BankID
            WHERE e.BankBranchID IS NULL
              AND e.BankBranch IS NOT NULL
              AND e.BankID IS NOT NULL
        ");

        Schema::table('t_HREmployees', function (Blueprint $table) {
            if (Schema::hasColumn('t_HREmployees', 'BankID')) {
                $table->foreign('BankID')->references('BankID')->on('t_Banks');
            }
            if (Schema::hasColumn('t_HREmployees', 'BankBranchID')) {
                $table->foreign('BankBranchID')->references('BranchID')->on('t_BankBranches');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HREmployees', function (Blueprint $table) {
            if (Schema::hasColumn('t_HREmployees', 'BankBranchID')) {
                $table->dropForeign(['BankBranchID']);
                $table->dropColumn('BankBranchID');
            }
            if (Schema::hasColumn('t_HREmployees', 'BankID')) {
                $table->dropForeign(['BankID']);
                $table->dropColumn('BankID');
            }
        });
    }
};
