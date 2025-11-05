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
        Schema::table('t_BancassuranceBeneficiaries', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CustomerID'])->references(['Id'])->on('t_BancassuranceCustomers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BancassuranceBeneficiaries', function (Blueprint $table) {
            $table->dropForeign('t_bancassurancebeneficiaries_createdby_foreign');
            $table->dropForeign('t_bancassurancebeneficiaries_customerid_foreign');
            $table->dropForeign('t_bancassurancebeneficiaries_deletedby_foreign');
            $table->dropForeign('t_bancassurancebeneficiaries_modifiedby_foreign');
        });
    }
};
