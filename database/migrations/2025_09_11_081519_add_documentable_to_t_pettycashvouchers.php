<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_PettyCashVouchers', function (Blueprint $table) {
            $table->id('VoucherID');

            $table->unsignedBigInteger('FloatID');      // t_PettyCashFloats.FloatID
            $table->string('VoucherType', 20);          // DISBURSEMENT|REPLENISHMENT|REFUND|ADJUSTMENT
            $table->string('DocNo', 50)->nullable();    // if you want numbering later
            $table->date('DocDate');
            $table->unsignedBigInteger('CurrencyID');   // same as float currency (recommended)
            $table->decimal('ExchangeRate', 18, 6)->default(1);
            $table->decimal('Amount', 18, 2);           // header total
            $table->string('Status', 20)->default('Draft'); // Draft|Posted|Voided
            $table->string('Reference', 100)->nullable();
            $table->string('Narration', 300)->nullable();

            // Bank side (for top-up/refund)
            $table->unsignedBigInteger('BankAccountID')->nullable(); // t_BankAccounts.AccountID

            // Posting info
            $table->unsignedBigInteger('CashbookID')->nullable();  // link if we create a cashbook entry
            $table->dateTime('PostedOn')->nullable();
            $table->unsignedBigInteger('PostedBy')->nullable();
            $table->dateTime('VoidedOn')->nullable();
            $table->unsignedBigInteger('VoidedBy')->nullable();

            // Audit
            $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();

            $table->foreign('FloatID')->references('FloatID')->on('t_PettyCashFloats');
            $table->foreign('CurrencyID')->references('Id')->on('t_Currencies');
            $table->foreign('BankAccountID')->references('AccountID')->on('t_BankAccounts');
            $table->index(['FloatID','VoucherType','Status']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('t_PettyCashVouchers');
    }
};
