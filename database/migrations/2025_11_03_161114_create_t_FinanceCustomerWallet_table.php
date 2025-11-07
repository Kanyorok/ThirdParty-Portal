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
        Schema::create('t_FinanceCustomerWallet', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('CustomerID')->unique();
            $table->decimal('Balance', 15)->default(0);
            $table->decimal('TotalDeposits', 15)->default(0);
            $table->decimal('TotalWithdrawals', 15)->default(0);
            $table->boolean('IsActive')->default(true);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec07515c6793');
            $table->index(['CustomerID', 'IsActive']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FinanceCustomerWallet');
    }
};
