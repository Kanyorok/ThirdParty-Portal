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
        Schema::create('t_GLClosingBalances', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('GLAccountID')->constrained('t_FinanceGLAccounts','Id');
            $table->foreignId('BranchID')->nullable()->constrained('t_Branches', 'Id');
            $table->dateTime('BalanceDate ')->unique();
            $table->decimal('OpeningBalance', 18, 5)->default(0.00000);
            $table->decimal('ClosingBalance', 18, 5)->default(0.00000);

            $table->decimal('LocalBalance', 18, 5)->default(0.00000);
            $table->decimal('ForeignBalance', 18, 5)->default(0.00000);

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
        Schema::dropIfExists('t_GLClosingBalances');
    }
};

