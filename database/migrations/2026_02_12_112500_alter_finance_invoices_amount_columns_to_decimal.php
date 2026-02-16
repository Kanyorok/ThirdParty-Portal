<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlsrv') {
            DB::statement('ALTER TABLE [t_FinanceInvoices] ALTER COLUMN [TotalAmount] DECIMAL(18, 2) NULL');
            DB::statement('ALTER TABLE [t_FinanceInvoices] ALTER COLUMN [AmountPaid] DECIMAL(18, 2) NULL');

            return;
        }

        Schema::table('t_FinanceInvoices', function (Blueprint $table) {
            $table->decimal('TotalAmount', 18, 2)->nullable()->change();
            $table->decimal('AmountPaid', 18, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlsrv') {
            DB::statement('UPDATE [t_FinanceInvoices] SET [TotalAmount] = ROUND(COALESCE([TotalAmount], 0), 0), [AmountPaid] = ROUND(COALESCE([AmountPaid], 0), 0)');
            DB::statement('ALTER TABLE [t_FinanceInvoices] ALTER COLUMN [TotalAmount] INT NULL');
            DB::statement('ALTER TABLE [t_FinanceInvoices] ALTER COLUMN [AmountPaid] INT NULL');

            return;
        }

        Schema::table('t_FinanceInvoices', function (Blueprint $table) {
            $table->integer('TotalAmount')->nullable()->change();
            $table->integer('AmountPaid')->nullable()->change();
        });
    }
};
