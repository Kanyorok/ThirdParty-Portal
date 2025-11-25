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
        Schema::table('t_FinancialTransactions', function (Blueprint $table) {
            //Add a new column to the t_FinancialTransactions table to store the third party type
            $table->foreignId('ThirdPartyTypeID')->nullable()->constrained('t_ThirdPartyTypes', 'TypeId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinancialTransactions', function (Blueprint $table) {
            //Drop the new column from the t_FinancialTransactions table
            $table->dropForeign(['ThirdPartyTypeID']);
            $table->dropColumn('ThirdPartyTypeID');
        });
    }
};
