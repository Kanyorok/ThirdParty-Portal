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
        Schema::create('t_TransactionReceipts', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('ReceiptId')->nullable();
            $table->bigInteger('TransferId');
            $table->date('ReceivedDate');
            $table->text('GeneralRemarks')->nullable();
            $table->string('Status')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('ReceivedBy')->nullable();

            $table->primary(['Id'], 'pk__t_transa__3214ec070236e556');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_TransactionReceipts');
    }
};
