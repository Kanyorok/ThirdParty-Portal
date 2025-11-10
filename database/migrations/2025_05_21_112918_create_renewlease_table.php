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
        Schema::create('t_RenewLease', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('LeaseNumber')->constrained('t_LeaseCreation', 'Id');
            $table->foreignId('TenantId')->constrained('t_LeaseCreation', 'Id');
            $table->foreignId('PropertyId')->constrained('t_LeaseCreation', 'Id');
            $table->foreignId('PaymentFrequency')->constrained('t_CodeDetails', 'ID');
            $table->date('EndDateCurrentLease');
            $table->date('NewStartDate');
            $table->date('NewEndDate');
            $table->integer('NewMonthlyRent');
            $table->string('Remarks');
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
        Schema::dropIfExists('t_RenewLease');
    }
};
