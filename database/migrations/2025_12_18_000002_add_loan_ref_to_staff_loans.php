<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_HRStaffLoans', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRStaffLoans', 'LoanRef')) {
                $table->string('LoanRef', 100)->nullable()->after('EmployeeID');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRStaffLoans', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRStaffLoans', 'LoanRef')) {
                $table->dropColumn('LoanRef');
            }
        });
    }
};

