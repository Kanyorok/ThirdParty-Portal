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
        Schema::table('t_InsuranceProductRiders', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['InsuranceProviderId'])->references(['Id'])->on('t_InsuranceProviders')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Product'])->references(['Id'])->on('t_InsuranceProducts')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_InsuranceProductRiders', function (Blueprint $table) {
            $table->dropForeign('t_insuranceproductriders_createdby_foreign');
            $table->dropForeign('t_insuranceproductriders_deletedby_foreign');
            $table->dropForeign('t_insuranceproductriders_insuranceproviderid_foreign');
            $table->dropForeign('t_insuranceproductriders_modifiedby_foreign');
            $table->dropForeign('t_insuranceproductriders_product_foreign');
        });
    }
};
