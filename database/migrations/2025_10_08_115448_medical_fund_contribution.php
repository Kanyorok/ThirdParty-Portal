<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_MedicalFundContributions', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('FundId')->constrained('t_MedicalFunds', 'Id');
            $table->foreignId('ContributorType')->constrained('t_CodeDetails','ID'); // e.g., Employee, Employer
            $table->unsignedBigInteger('ContributorId')->nullable(); // Optional link to employee/party table
            $table->decimal('Amount', 18, 2);
            $table->date('ContributionDate');
            $table->string('Notes', 500)->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_MedicalFundContributions');
    }
};
