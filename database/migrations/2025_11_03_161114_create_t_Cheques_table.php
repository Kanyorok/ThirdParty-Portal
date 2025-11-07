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
        Schema::create('t_Cheques', function (Blueprint $table) {
            $table->bigIncrements('ChequeID');
            $table->string('Direction', 10);
            $table->bigInteger('BankAccountID')->nullable();
            $table->bigInteger('ChequeBookID')->nullable();
            $table->string('ChequeNumber', 50);
            $table->date('ChequeDate')->nullable();
            $table->date('DueDate')->nullable();
            $table->boolean('IsPostDated')->default(false);
            $table->bigInteger('CurrencyID');
            $table->decimal('Amount', 18);
            $table->string('PartyType', 20)->nullable();
            $table->bigInteger('PartyID')->nullable();
            $table->string('PartyName', 200)->nullable();
            $table->string('Status', 20)->default('Draft');
            $table->date('ReceivedDate')->nullable();
            $table->date('DepositDate')->nullable();
            $table->date('ClearDate')->nullable();
            $table->date('BounceDate')->nullable();
            $table->bigInteger('CashbookID_Deposit')->nullable();
            $table->bigInteger('CashbookID_Clear')->nullable();
            $table->string('Reference', 100)->nullable();
            $table->string('Narration', 300)->nullable();
            $table->boolean('IsActive')->default(true);
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();

            $table->primary(['ChequeID'], 'pk__t_cheque__b816d9d03548d407');
            $table->unique(['ChequeBookID', 'ChequeNumber']);
            $table->index(['Direction', 'Status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Cheques');
    }
};
