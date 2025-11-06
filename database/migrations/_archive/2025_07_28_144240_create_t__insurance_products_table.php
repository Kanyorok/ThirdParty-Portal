<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_InsuranceProducts', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('InsuranceProviderID')->constrained('t_InsuranceProviders', 'Id');
            $table->string('Name');
            $table->string('Type');
            $table->string('Description');
            $table->boolean('IsActive');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('DeletedOn')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_InsuranceProducts');
    }
};
