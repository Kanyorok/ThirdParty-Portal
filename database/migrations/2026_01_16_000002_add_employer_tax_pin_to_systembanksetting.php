<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('t_SystemBankSetting') && !Schema::hasColumn('t_SystemBankSetting', 'EmployerTaxPIN')) {
            Schema::table('t_SystemBankSetting', function (Blueprint $table) {
                $table->string('EmployerTaxPIN', 50)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('t_SystemBankSetting') && Schema::hasColumn('t_SystemBankSetting', 'EmployerTaxPIN')) {
            Schema::table('t_SystemBankSetting', function (Blueprint $table) {
                $table->dropColumn('EmployerTaxPIN');
            });
        }
    }
};
