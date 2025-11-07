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
        Schema::table('t_BancassurancePremiumPayments ', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PaymentMode'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PolicyID'])->references(['Id'])->on('t_BancassurancePolicies')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BancassurancePremiumPayments ', function (Blueprint $table) {
            $table->dropForeign('t_bancassurancepremiumpayments _createdby_foreign');
            $table->dropForeign('t_bancassurancepremiumpayments _deletedby_foreign');
            $table->dropForeign('t_bancassurancepremiumpayments _modifiedby_foreign');
            $table->dropForeign('t_bancassurancepremiumpayments _paymentmode_foreign');
            $table->dropForeign('t_bancassurancepremiumpayments _policyid_foreign');
        });
    }
};
