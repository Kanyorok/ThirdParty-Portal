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
        Schema::table('t_LeaseCreation', function (Blueprint $table) {
            $table->foreign(['BlockID'])->references(['Id'])->on('t_PropertyBlock')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['FloorID'])->references(['Id'])->on('t_PropertyFloor')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PaymentFrequency'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PropertyID'])->references(['Id'])->on('t_PropertyRegistry')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Tenant'])->references(['Id'])->on('t_TenantMaintenance')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Unit'])->references(['Id'])->on('t_PropertyUnit')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_LeaseCreation', function (Blueprint $table) {
            $table->dropForeign('t_leasecreation_blockid_foreign');
            $table->dropForeign('t_leasecreation_createdby_foreign');
            $table->dropForeign('t_leasecreation_deletedby_foreign');
            $table->dropForeign('t_leasecreation_floorid_foreign');
            $table->dropForeign('t_leasecreation_modifiedby_foreign');
            $table->dropForeign('t_leasecreation_paymentfrequency_foreign');
            $table->dropForeign('t_leasecreation_propertyid_foreign');
            $table->dropForeign('t_leasecreation_tenant_foreign');
            $table->dropForeign('t_leasecreation_unit_foreign');
        });
    }
};
