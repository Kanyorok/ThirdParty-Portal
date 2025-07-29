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
        Schema::create('t_BancassuranceReferrals', function (Blueprint $table) {
            $table->id('Id');
            $table->String('ClientName');
            $table->String('ClientIDNumber');
            $table->String('ClientPhone');
            $table->String('ClientEmail');
            $table->foreignId('BranchId')->constrained('t_Branches', 'Id');
            $table->foreignId('RefferedBy')->constrained('t_Users', 'Id');
            $table->date('ReferralDate');
            $table->foreignId('InsuranceProdeuctId')->constrained('t_CodeDetails','ID');
            $table->foreignId('PreferredInsurerId')->constrained('t_CodeDetails','ID');
            $table->String('Remarks');
            $table->String('Status');
            $table->String('AssignedTo');
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
        Schema::dropIfExists('t_BancassuranceReferrals');
    }
};
