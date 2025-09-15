<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('t_PettyCashReplenishmentBatches', function (Blueprint $table) {
            $table->id('BatchID');
            $table->unsignedBigInteger('FloatID');
            $table->unsignedBigInteger('BankAccountID');
            $table->date('BatchDate');
            $table->decimal('TotalAmount', 18, 2)->default(0);
            $table->string('Status', 20)->default('Draft'); // Draft|Posted|Voided
            $table->unsignedBigInteger('CashbookID')->nullable();

            // Audit
            $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();

            $table->foreign('FloatID')->references('FloatID')->on('t_PettyCashFloats');
            $table->foreign('BankAccountID')->references('AccountID')->on('t_BankAccounts');
            $table->index(['FloatID','Status']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('t_PettyCashReplenishmentBatches');
    }
};
