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
        Schema::table('t_FinanceCreditHolds', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreditID'])->references(['Id'])->on('t_FinanceCreditManagement')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CustomerID'])->references(['Id'])->on('t_ThirdParties')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceCreditHolds', function (Blueprint $table) {
            $table->dropForeign('t_financecreditholds_createdby_foreign');
            $table->dropForeign('t_financecreditholds_creditid_foreign');
            $table->dropForeign('t_financecreditholds_customerid_foreign');
            $table->dropForeign('t_financecreditholds_deletedby_foreign');
            $table->dropForeign('t_financecreditholds_modifiedby_foreign');
        });
    }
};
