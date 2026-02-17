<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('t_HREmployees', 'BankName')) {
            DB::statement("
                UPDATE e
                SET e.BankID = b.BankID
                FROM t_HREmployees e
                INNER JOIN t_Banks b ON b.BankName = e.BankName
                WHERE e.BankID IS NULL AND e.BankName IS NOT NULL
            ");
        }

        if (Schema::hasColumn('t_HREmployees', 'BankBranch')) {
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
        }

        Schema::table('t_HREmployees', function (Blueprint $table) {
            if (Schema::hasColumn('t_HREmployees', 'BankName')) {
                $table->dropColumn('BankName');
            }
            if (Schema::hasColumn('t_HREmployees', 'BankBranch')) {
                $table->dropColumn('BankBranch');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HREmployees', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HREmployees', 'BankName')) {
                $table->string('BankName', 150)->nullable()->after('PaymentMode');
            }
            if (!Schema::hasColumn('t_HREmployees', 'BankBranch')) {
                $table->string('BankBranch', 150)->nullable()->after('BankName');
            }
        });
    }
};
