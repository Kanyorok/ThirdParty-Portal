<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_Cashbook', function (Blueprint $table) {
            $table->id('CashbookID');

            $table->unsignedBigInteger('BankAccountID');         // t_BankAccounts.AccountID
            $table->unsignedBigInteger('CurrencyID')->nullable();// t_Currencies.Id
            $table->decimal('ExchangeRate', 18, 6)->default(1);

            $table->date('DocDate');
            $table->string('DocNo', 50)->nullable();

            // RECEIPT | PAYMENT
            $table->string('EntryType', 20); // enum-like
            $table->string('TransactionType', 50)->nullable()->after('EntryType'); // e.g. CB_BANK_CHARGE
            $table->boolean('UseAutoGL')->default(1)->after('TransactionType');

            // Party info (optional if manual)
            $table->string('PartyType', 20)->nullable(); // CUSTOMER | VENDOR | OTHER
            $table->unsignedBigInteger('PartyID')->nullable();   // FK to your master tables if needed
            $table->string('PartyName', 200)->nullable();

            $table->string('Reference', 100)->nullable();
            $table->string('Narration', 500)->nullable();

            $table->decimal('Amount', 18, 2);         // transaction amount in Tx currency
            $table->decimal('AmountBase', 18, 2)->default(0); // optional: base value

            // MANUAL | AR | AP | PC (petty cash) | SYS (system generated)
            $table->string('SourceModule', 20)->default('MANUAL');
            $table->unsignedBigInteger('SourceID')->nullable(); // e.g. AR invoice id / AP voucher id
            $table->boolean('IsSystemGenerated')->default(0);

            // Draft | Posted | Voided
            $table->string('Status', 20)->default('Draft');
            $table->dateTime('PostedOn')->nullable();
            $table->unsignedBigInteger('PostedBy')->nullable();
            $table->dateTime('VoidedOn')->nullable();
            $table->unsignedBigInteger('VoidedBy')->nullable();

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
        Schema::dropIfExists('t_Cashbook');
    }
};
