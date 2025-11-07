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
        Schema::table('t_FinanceGLAccounts', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['GLSubAccountTypeID'])->references(['Id'])->on('t_FinanceGLSubAccountTypes')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['GLTypeGroupID'])->references(['Id'])->on('t_FinanceGLTypeGroups')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceGLAccounts', function (Blueprint $table) {
            $table->dropForeign('t_financeglaccounts_createdby_foreign');
            $table->dropForeign('t_financeglaccounts_deletedby_foreign');
            $table->dropForeign('t_financeglaccounts_glsubaccounttypeid_foreign');
            $table->dropForeign('t_financeglaccounts_gltypegroupid_foreign');
            $table->dropForeign('t_financeglaccounts_modifiedby_foreign');
        });
    }
};
