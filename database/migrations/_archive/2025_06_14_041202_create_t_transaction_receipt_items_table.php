<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('t_TransactionReceiptItems', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('ReceiptId')->constrained('t_TransactionReceipts', 'Id');
            $table->foreignId('Item')->constrained('t_Items', 'Id');
            $table->string('DispatchedQty');
            $table->string('ReceivedQty')->nullable();
            $table->string('DamagedQty')->nullable();
            $table->string('Discrepancy');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    public function down()
    {
        Schema::dropIfExists('t_TransactionReceiptItems');
    }
};
