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
        Schema::create('t_BankAccounts', function (Blueprint $table) {
            $table->bigIncrements('AccountID');
            $table->bigInteger('BankID');
            $table->bigInteger('BranchID')->nullable();
            $table->string('AccountName', 150)->nullable();
            $table->string('AccountNumber', 50);
            $table->string('IBAN', 34)->nullable();
            $table->bigInteger('CurrencyID')->nullable();
            $table->bigInteger('GLAccountID')->nullable();
            $table->decimal('OpeningBalance', 18)->default(0);
            $table->decimal('CurrentBalance', 18)->default(0);
            $table->boolean('IsDefault')->default(false);
            $table->boolean('IsActive')->default(true);
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();

            $table->primary(['AccountID'], 'pk__t_bankac__349da586a1be9650');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BankAccounts');
    }
};
