<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_Cheques', function (Blueprint $table) {
            $table->id('ChequeID');

            $table->string('Direction', 10); // ISSUED | RECEIVED
            $table->unsignedBigInteger('BankAccountID')->nullable(); // bank to be debited/credited (for ISSUED: source; for RECEIVED: deposit-to when depositing)

            $table->unsignedBigInteger('ChequeBookID')->nullable();  // only for ISSUED
            $table->string('ChequeNumber', 50);
            $table->date('ChequeDate')->nullable();
            $table->date('DueDate')->nullable();     // post-dated support
            $table->boolean('IsPostDated')->default(0);

            $table->unsignedBigInteger('CurrencyID');
            $table->decimal('Amount', 18, 2);

            // Party info
            $table->string('PartyType', 20)->nullable(); // CUSTOMER | VENDOR | OTHER
            $table->unsignedBigInteger('PartyID')->nullable();
            $table->string('PartyName', 200)->nullable();

            // Lifecycle
            $table->string('Status', 20)->default('Draft'); // Draft|Issued|OnHand|Deposited|Cleared|Bounced|Cancelled
            $table->date('ReceivedDate')->nullable(); // for RECEIVED
            $table->date('DepositDate')->nullable();  // for RECEIVED
            $table->date('ClearDate')->nullable();    // for both
            $table->date('BounceDate')->nullable();

            // Links to Cashbook entries (created by actions)
            $table->unsignedBigInteger('CashbookID_Deposit')->nullable(); // RECEIPT when depositing a received cheque
            $table->unsignedBigInteger('CashbookID_Clear')->nullable();   // PAYMENT (issued) or RECEIPT (received) when clearing

            $table->string('Reference', 100)->nullable();
            $table->string('Narration', 300)->nullable();

            $table->boolean('IsActive')->default(1);

            // audit
            $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();

            // FKs
            $table->foreign('BankAccountID')->references('AccountID')->on('t_BankAccounts');
            $table->foreign('ChequeBookID')->references('ChequeBookID')->on('t_ChequeBooks');
            $table->foreign('CurrencyID')->references('Id')->on('t_Currencies');

            $table->index(['Direction', 'Status']);
            $table->unique(['ChequeBookID', 'ChequeNumber']); // prevents duplicate leaves per book
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_Cheques');
    }
};
