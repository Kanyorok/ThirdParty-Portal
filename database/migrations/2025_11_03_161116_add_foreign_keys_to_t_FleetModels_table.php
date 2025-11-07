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
        Schema::table('t_FleetModels', function (Blueprint $table) {
            $table->foreign(['BrandID'])->references(['Id'])->on('t_FleetBrands')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FleetModels', function (Blueprint $table) {
            $table->dropForeign('t_fleetmodels_brandid_foreign');
            $table->dropForeign('t_fleetmodels_createdby_foreign');
            $table->dropForeign('t_fleetmodels_deletedby_foreign');
            $table->dropForeign('t_fleetmodels_modifiedby_foreign');
        });
    }
};
