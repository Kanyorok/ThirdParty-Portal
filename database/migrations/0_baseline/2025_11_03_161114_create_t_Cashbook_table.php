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
        Schema::create('t_Cashbook', function (Blueprint $table) {
            $table->bigIncrements('CashbookID');
            $table->bigInteger('BankAccountID');
            $table->bigInteger('CurrencyID')->nullable();
            $table->decimal('ExchangeRate', 18, 6)->default(1);
            $table->date('DocDate');
            $table->string('DocNo', 50)->nullable();
            $table->string('EntryType', 20);
            $table->string('TransactionType', 50)->nullable();
            $table->boolean('UseAutoGL')->default(true);
            $table->string('PartyType', 20)->nullable();
            $table->bigInteger('PartyID')->nullable();
            $table->string('PartyName', 200)->nullable();
            $table->string('Reference', 100)->nullable();
            $table->string('Narration', 500)->nullable();
            $table->decimal('Amount', 18);
            $table->decimal('AmountBase', 18)->default(0);
            $table->string('SourceModule', 20)->default('MANUAL');
            $table->bigInteger('SourceID')->nullable();
            $table->boolean('IsSystemGenerated')->default(false);
            $table->string('Status', 20)->default('Draft');
            $table->dateTime('PostedOn')->nullable();
            $table->bigInteger('PostedBy')->nullable();
            $table->dateTime('VoidedOn')->nullable();
            $table->bigInteger('VoidedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();

            $table->primary(['CashbookID'], 'pk__t_cashbo__0757d73b850bcc39');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Cashbook');
    }
};
