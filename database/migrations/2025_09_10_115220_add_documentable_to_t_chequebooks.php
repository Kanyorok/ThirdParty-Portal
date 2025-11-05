<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_ChequeBooks', function (Blueprint $table) {
            $table->id('ChequeBookID');
            $table->unsignedBigInteger('BankAccountID'); // t_BankAccounts.AccountID
            $table->string('BookName', 100)->nullable();
            $table->string('Prefix', 20)->nullable();
            $table->string('Suffix', 20)->nullable();

            $table->unsignedBigInteger('StartNumber');     // first leaf number
            $table->unsignedBigInteger('EndNumber');       // last leaf number
            $table->unsignedBigInteger('NextLeafNumber');  // next number to propose
            $table->unsignedInteger('LeavesTotal');
            $table->unsignedInteger('LeavesIssued')->default(0);

            $table->boolean('IsActive')->default(1);

            // audit
            $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();

            $table->foreign('BankAccountID')->references('AccountID')->on('t_BankAccounts');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_ChequeBooks');
    }
};
