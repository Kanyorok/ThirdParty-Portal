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
        Schema::create('t_LegalLoanSecurities', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('SecurityType');
            $table->string('OwnerName');
            $table->string('OwnerIDNumber');
            $table->string('LoanAccountNumber');
            $table->decimal('Value', 18);
            $table->string('Institution');
            $table->string('RegistrationDetails');
            $table->string('Locations');
            $table->string('SecurityStatus', 50)->default('Held');
            $table->text('Remarks');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_legall__3214ec0713c8805e');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_LegalLoanSecurities');
    }
};
