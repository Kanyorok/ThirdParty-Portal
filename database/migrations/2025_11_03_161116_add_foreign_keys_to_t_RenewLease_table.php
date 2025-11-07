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
        Schema::table('t_RenewLease', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['LeaseNumber'])->references(['Id'])->on('t_LeaseCreation')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PaymentFrequency'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RenewLease', function (Blueprint $table) {
            $table->dropForeign('t_renewlease_createdby_foreign');
            $table->dropForeign('t_renewlease_deletedby_foreign');
            $table->dropForeign('t_renewlease_leasenumber_foreign');
            $table->dropForeign('t_renewlease_modifiedby_foreign');
            $table->dropForeign('t_renewlease_paymentfrequency_foreign');
        });
    }
};
