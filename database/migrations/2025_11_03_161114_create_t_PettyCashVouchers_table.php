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
        Schema::create('t_PettyCashVouchers', function (Blueprint $table) {
            $table->bigIncrements('VoucherID');
            $table->bigInteger('FloatID');
            $table->string('VoucherType', 20);
            $table->string('DocNo', 50)->nullable();
            $table->date('DocDate');
            $table->bigInteger('CurrencyID');
            $table->decimal('ExchangeRate', 18, 6)->default(1);
            $table->decimal('Amount', 18);
            $table->string('Status', 20)->default('Draft');
            $table->string('Reference', 100)->nullable();
            $table->string('Narration', 300)->nullable();
            $table->bigInteger('BankAccountID')->nullable();
            $table->bigInteger('CashbookID')->nullable();
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
            $table->string('ApprovalStatus', 20)->default('N/A');
            $table->dateTime('SubmittedOn')->nullable();
            $table->bigInteger('SubmittedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->bigInteger('ApprovedBy')->nullable();
            $table->bigInteger('ReplenishmentBatchID')->nullable()->index();

            $table->primary(['VoucherID'], 'pk__t_pettyc__3aee79c1bb22d774');
            $table->index(['FloatID', 'VoucherType', 'Status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_PettyCashVouchers');
    }
};
