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
        Schema::create('t_BancassurancePolicies', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('CustomerID')->Constrained('t_BancassuranceCustomers','Id');
            $table->foreignId('ProductID')->Constrained('t_InsuranceProducts','Id');
            $table->foreignId('InsurerID')->Constrained('t_InsuranceProviders','Id')->nullable();
            $table->string('PolicyNumber');
            $table->float('SumAssured',18,2);
            $table->float('PremiumAmount',18,2);
            $table->date('PolicyStartDate');
            $table->date('PolicyEndDate');
            $table->foreignId('PaymentFrequency')->Constrained('t_CodeDetails','ID');
            $table->foreignId('ReferralID')->Constrained('t_BancassuranceReferrals','Id')->nullable();
            $table->date('IssuedDate');
            $table->date('ExpiryDate');
            $table->boolean('IsActive');
            $table->string('Status');
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
        Schema::dropIfExists('t_BancassurancePolicies');
    }
};
