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
        Schema::create('t_LegalLoanSecurities', function (Blueprint $table) {
            $table->id('Id');
            $table->string('SecurityType');
            $table->string('OwnerName');
            $table->string('OwnerIDNumber');
            $table->string('LoanAccountNumber');
            $table->decimal('Value', 18, 2);
            $table->string('Institution');
            $table->string('RegistrationDetails');
            $table->string('Locations', 255);
            $table->string('SecurityStatus', 50)->default('Held');
            $table->text('Remarks');

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
        Schema::dropIfExists('t_LegalLoanSecurities');
    }
};
