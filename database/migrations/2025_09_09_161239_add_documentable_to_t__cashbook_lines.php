<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_CashbookLines', function (Blueprint $table) {
            $table->id('LineID');
            $table->unsignedBigInteger('CashbookID');
            $table->unsignedBigInteger('GLAccountID')->nullable(); // link to your GL account
            $table->string('Description', 300)->nullable();

            // For a receipt, credit bank, debit one or many GLs (AmountDr here)
            // For a payment, debit bank, credit one or many GLs (AmountCr here)
            $table->decimal('AmountDr', 18, 2)->default(0);
            $table->decimal('AmountCr', 18, 2)->default(0);

            $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();

            $table->foreign('CashbookID')->references('CashbookID')->on('t_Cashbook');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_CashbookLines');
    }
};
