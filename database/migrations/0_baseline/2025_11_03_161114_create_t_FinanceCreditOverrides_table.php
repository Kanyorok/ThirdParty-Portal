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
        Schema::create('t_FinanceCreditOverrides', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('CreditID');
            $table->bigInteger('CustomerID');
            $table->decimal('AllowedAmount', 18);
            $table->string('Reason')->nullable();
            $table->string('Status', 20)->default('Active');
            $table->dateTime('ApprovedOn')->useCurrent();
            $table->bigInteger('ApprovedBy');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn')->useCurrent();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec0705440aeb');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FinanceCreditOverrides');
    }
};
