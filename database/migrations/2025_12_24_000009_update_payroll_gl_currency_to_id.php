<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('t_HRPayrollGLSettings', 'CurrencyID')) {
            Schema::table('t_HRPayrollGLSettings', function (Blueprint $table) {
                $table->unsignedBigInteger('CurrencyID')->nullable();
            });
            return;
        }

        if (!Schema::hasColumn('t_HRPayrollGLSettings', 'CurrencyIDNew')) {
            Schema::table('t_HRPayrollGLSettings', function (Blueprint $table) {
                $table->unsignedBigInteger('CurrencyIDNew')->nullable();
            });
        }

        DB::statement("
            UPDATE gl
            SET gl.CurrencyIDNew = c.Id
            FROM t_HRPayrollGLSettings gl
            INNER JOIN t_Currencies c ON c.Code = gl.CurrencyID
            WHERE gl.CurrencyIDNew IS NULL
              AND gl.CurrencyID IS NOT NULL
        ");

        if (Schema::hasColumn('t_HRPayrollGLSettings', 'CurrencyID')) {
            Schema::table('t_HRPayrollGLSettings', function (Blueprint $table) {
                $table->dropColumn('CurrencyID');
            });
        }

        DB::statement("EXEC sp_rename 't_HRPayrollGLSettings.CurrencyIDNew', 'CurrencyID', 'COLUMN'");
    }

    public function down(): void
    {
        if (!Schema::hasColumn('t_HRPayrollGLSettings', 'CurrencyID')) {
            return;
        }

        if (!Schema::hasColumn('t_HRPayrollGLSettings', 'CurrencyCode')) {
            Schema::table('t_HRPayrollGLSettings', function (Blueprint $table) {
                $table->string('CurrencyCode', 10)->nullable();
            });
        }

        DB::statement("
            UPDATE gl
            SET gl.CurrencyCode = c.Code
            FROM t_HRPayrollGLSettings gl
            INNER JOIN t_Currencies c ON c.Id = gl.CurrencyID
            WHERE gl.CurrencyID IS NOT NULL
        ");
    }
};
