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
        Schema::create('t_Vehicles', function (Blueprint $table) {

            $table->id('Id');
            $table->string('RegistrationNo')->unique();
            $table->foreignId('Make')->constrained('t_FleetBrands', 'Id');
            $table->foreignId('Model')->constrained('t_FleetModels', 'Id');
            $table->integer('Year')->nullable();
            $table->string('Color')->nullable();
            $table->string('ChassisNo')->unique();
            $table->string('EngineNo')->nullable();
            $table->foreignId('Type')->constrained('t_CodeDetails', 'ID');         
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Vehicles');
    }
};
