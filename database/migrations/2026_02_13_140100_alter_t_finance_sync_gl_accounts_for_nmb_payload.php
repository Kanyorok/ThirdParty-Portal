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
        Schema::table('t_FinanceSyncGLAccounts', function (Blueprint $table) {
            $table->dropForeign(['GLTypeGroupID']);
            $table->dropForeign(['GLSubAccountTypeID']);
        });

        Schema::table('t_FinanceSyncGLAccounts', function (Blueprint $table) {
            $table->unsignedBigInteger('GLTypeGroupID')->nullable()->change();
            $table->unsignedBigInteger('GLSubAccountTypeID')->nullable()->change();
            $table->string('Source', 100)->nullable()->after('CurrencyID');
            $table->string('SourceTable', 255)->nullable()->after('Source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceSyncGLAccounts', function (Blueprint $table) {
            $table->dropColumn(['Source', 'SourceTable']);
            $table->unsignedBigInteger('GLTypeGroupID')->nullable(false)->change();
            $table->unsignedBigInteger('GLSubAccountTypeID')->nullable(false)->change();
            $table->foreign('GLTypeGroupID')->references('Id')->on('t_FinanceGLTypeGroups');
            $table->foreign('GLSubAccountTypeID')->references('Id')->on('t_FinanceGLSubAccountTypes');
        });
    }
};
