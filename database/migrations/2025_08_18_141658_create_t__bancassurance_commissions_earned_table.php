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
        Schema::create('t_BancassuranceCommissionsEarned', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('PolicyId')->constrained('t_BancassurancePolicies', 'Id');
            $table->foreignId('ReferralId')->constrained('t_BancassuranceReferrals', 'Id');
            $table->foreignId('CommissionRuleId')->constrained('t_BancassuranceCommissionRules', 'Id');
            $table->foreignId('EarnedByType')->constrained('t_CodeDetails','ID');
            $table->string('EarnedById');
            $table->float('EarnedAmount');
            $table->string('Status');
            $table->date('EarnedDate');
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
        Schema::dropIfExists('t_BancassuranceCommissionsEarned');
    }
};
