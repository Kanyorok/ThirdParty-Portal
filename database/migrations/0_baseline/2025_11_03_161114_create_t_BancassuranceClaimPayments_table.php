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
        Schema::create('t_BancassuranceClaimPayments', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('ClaimId');
            $table->date('PaymentDate');
            $table->float('PaymentAmount');
            $table->string('PaymentReference');
            $table->string('Note');
            $table->string('PaidBy');
            $table->bigInteger('PaymentMethod');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_bancas__3214ec07f2d0f92d');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BancassuranceClaimPayments');
    }
};
