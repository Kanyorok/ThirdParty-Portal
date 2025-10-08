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
        Schema::create('t_MedicalFunds', function (Blueprint $table) {
            $table->id('Id');
            $table->string('FundName');
            $table->foreignId('ProviderId')->constrained('t_InsuranceProviders','Id');
            $table->foreignId('CoverageType')->constrained('t_CodeDetails','ID');
            $table->decimal('CoverageLimit', 18, 2)->nullable();
            $table->text('Description')->nullable();
            $table->boolean('IsActive')->default(true);
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
        Schema::dropIfExists('t_MedicalFunds');
    }
};
