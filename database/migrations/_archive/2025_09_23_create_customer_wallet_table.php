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
        Schema::create('t_FinanceCustomerWallet', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('CustomerID')->constrained('t_ThirdParties', 'Id');
            $table->decimal('Balance', 15, 2)->default(0);
            $table->decimal('TotalDeposits', 15, 2)->default(0);
            $table->decimal('TotalWithdrawals', 15, 2)->default(0);
            $table->boolean('IsActive')->default(true);

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->unique('CustomerID');
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
