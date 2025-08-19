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
        Schema::create('t_BancassuranceCommissionRules', function (Blueprint $table) {
            $table->id('Id');
            $table->string('RuleName');
            $table->foreignId('ProductId')->constrained('t_InsuranceProducts', 'Id');
            $table->foreignId('PolicyTypeId')->constrained('t_CodeDetails', 'ID');
            $table->float('CommissionRate');
            $table->float('FixedAmount');
            $table->foreignId('AppliesTo')->constrained('t_CodeDetails', 'ID');
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
        Schema::dropIfExists('t_BancassuranceCommissionRules');
    }
};
