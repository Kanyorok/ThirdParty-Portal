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
        Schema::table('t_InsuranceProducts', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['InsuranceProviderID'])->references(['Id'])->on('t_InsuranceProviders')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Type'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_InsuranceProducts', function (Blueprint $table) {
            $table->dropForeign('t_insuranceproducts_createdby_foreign');
            $table->dropForeign('t_insuranceproducts_deletedby_foreign');
            $table->dropForeign('t_insuranceproducts_insuranceproviderid_foreign');
            $table->dropForeign('t_insuranceproducts_modifiedby_foreign');
            $table->dropForeign('t_insuranceproducts_type_foreign');
        });
    }
};
