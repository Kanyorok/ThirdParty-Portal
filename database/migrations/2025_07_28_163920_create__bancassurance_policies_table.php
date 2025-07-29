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
            $table->foreignId('CustomerID')->constrained('t_BancassuranceCustomers', 'Id');
            $table->foreignId('ProductID')->constrained('t_InsuranceProducts', 'Id');
            $table->foreignId('ProviderID')->constrained('t_InsuranceProviders', 'Id');
            $table->string('PolicyNumber');
            $table->float('SumAssured');
            $table->float('PremiumAmount');
            $table->string('InsurerID');
            $table->date('PolicyStartDate');
            $table->date('PolicyEndDate');
            $table->foreignId('PremiumFrequency')->constrained('t_CodeDetails', 'ID');
            $table->string('ReferralID');
            $table->date('StartDate');
            $table->date('EndDate');
            $table->date('IssuedDate');
            $table->date('ExpiryDate');
            $table->string('PolicyDocumentPath');
            $table->float('InstallmentAmount');
            $table->float('AmountPaid');
            $table->date('NextInstallmentDueDate');
            $table->boolean('IsIssued');
            $table->foreignId('Status')->constrained('t_CodeDetails','ID');
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
