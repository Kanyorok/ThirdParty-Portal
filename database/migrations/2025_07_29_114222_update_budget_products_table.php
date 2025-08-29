<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateBudgetProductsTable extends Migration
{
    public function up(): void
    {
        // Drop the foreign key constraint if it exists
        $foreignKeyCheck = DB::select("
            SELECT name
            FROM sys.foreign_keys
            WHERE name = 't_budgetproducts_glaccountid_foreign'
        ");

        if (!empty($foreignKeyCheck)) {
            DB::statement("ALTER TABLE t_BudgetProducts DROP CONSTRAINT t_budgetproducts_glaccountid_foreign");
        }

        // Make the GLAccountID column nullable but do not add foreign key again
        Schema::table('t_BudgetProducts', function (Blueprint $table) {
            $table->unsignedBigInteger('GLAccountID')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Make the column NOT NULL again
        Schema::table('t_BudgetProducts', function (Blueprint $table) {
            $table->unsignedBigInteger('GLAccountID')->nullable(false)->change();
        });

        // Re-add the foreign key constraint (assuming original constraint referenced 't_BudgetGLAccounts.Id')
        Schema::table('t_BudgetProducts', function (Blueprint $table) {
            $table->foreign('GLAccountID', 't_budgetproducts_glaccountid_foreign')
                ->references('Id')
                ->on('t_BudgetGLAccounts')
                ->onUpdate('cascade')
                ->onDelete('no action');
        });
    }
}
