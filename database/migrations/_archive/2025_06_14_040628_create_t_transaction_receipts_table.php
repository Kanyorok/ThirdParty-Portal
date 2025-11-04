<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('t_TransactionReceipts', function (Blueprint $table) {
            $table->id('Id');
            $table->string('ReceiptId')->nullable();
            $table->foreignId('TransferId')->constrained('t_Transfers', 'Id');
            $table->string('ReceivedBy');
            $table->date('ReceivedDate');
            $table->text('GeneralRemarks')->nullable();
            $table->string('Status')->nullable();
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
        Schema::dropIfExists('t_TransactionReceipts');
    }
};
