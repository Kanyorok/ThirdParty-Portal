<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_BankAccounts', function (Blueprint $table) {
            $table->id('AccountID');

            $table->unsignedBigInteger('BankID');
            $table->unsignedBigInteger('BranchID')->nullable();

            $table->string('AccountName', 150)->nullable();
            $table->string('AccountNumber', 50);
            $table->string('IBAN', 34)->nullable();

            $table->unsignedBigInteger('CurrencyID')->nullable();
            $table->unsignedBigInteger('GLAccountID')->nullable();

            $table->decimal('OpeningBalance', 18, 2)->default(0);
            $table->decimal('CurrentBalance', 18, 2)->default(0);

            $table->boolean('IsDefault')->default(0);
            $table->boolean('IsActive')->default(1);

            $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();

            $table->foreign('BankID')->references('BankID')->on('t_Banks');
            $table->foreign('BranchID')->references('BranchID')->on('t_BankBranches');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_BankAccounts');
    }
};
