<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_PettyCashLines', function (Blueprint $table) {
            $table->id('LineID');
            $table->unsignedBigInteger('VoucherID');  // t_PettyCashVouchers.VoucherID
            $table->unsignedBigInteger('GLAccountID')->nullable(); // expense GL, if applicable
            $table->string('Description', 300)->nullable();
            $table->decimal('Amount', 18, 2)->default(0); // positive

            $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();

            $table->foreign('VoucherID')->references('VoucherID')->on('t_PettyCashVouchers');
            $table->index(['VoucherID']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('t_PettyCashLines');
    }
};
