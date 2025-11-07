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
        Schema::create('t_TenantMaintenance', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('TenantType');
            $table->string('Remarks');
            $table->boolean('IsActive')->default(true);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('ThirdPartyId')->nullable();

            $table->primary(['Id'], 'pk__t_tenant__3214ec0722222260');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_TenantMaintenance');
    }
};
