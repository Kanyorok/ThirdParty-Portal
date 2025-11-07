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
        Schema::table('t_TenantClearance', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DepositRefunded'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['LeaseId'])->references(['Id'])->on('t_LeaseCreation')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_TenantClearance', function (Blueprint $table) {
            $table->dropForeign('t_tenantclearance_createdby_foreign');
            $table->dropForeign('t_tenantclearance_deletedby_foreign');
            $table->dropForeign('t_tenantclearance_depositrefunded_foreign');
            $table->dropForeign('t_tenantclearance_leaseid_foreign');
            $table->dropForeign('t_tenantclearance_modifiedby_foreign');
        });
    }
};
