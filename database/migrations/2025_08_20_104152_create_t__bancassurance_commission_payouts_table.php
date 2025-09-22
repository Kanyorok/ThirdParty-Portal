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
        Schema::create('t_BancassuranceCommissionPayouts', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('PolicyId')->constrained('t_BancassurancePolicies', 'Id');
            $table->string('PayoutReference');
            $table->float('PaidAmount');
            $table->date('PaymentDate');
            $table->foreignId('PaymentMode')->constrained('t_CodeDetails', 'ID');
            $table->string('Remarks')->nullable();
            $table->foreignId('PaidBy')->constrained('t_Users', 'Id');
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
        Schema::dropIfExists('t_BancassuranceCommissionPayouts');
    }
};
