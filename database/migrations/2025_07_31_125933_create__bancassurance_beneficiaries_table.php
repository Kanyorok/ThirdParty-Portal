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
        Schema::create('t_BancassuranceBeneficiaries', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('CustomerID')->constrained('t_BancassuranceCustomers', 'Id');
            $table->string('PolicyID')->constrained('t_BancassurancePolicies', 'Id')->nullable;
            $table->string('FullName');
            $table->string('Relationship')->constrained('t_CodeDetails', 'ID');
            $table->string('IDNumber');
            $table->string('Phone');
            $table->string('Email');
            $table->float('PercentageShare');
            $table->boolean('IsPrimary ');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
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
        Schema::dropIfExists('t_BancassuranceBeneficiaries');
    }
};
