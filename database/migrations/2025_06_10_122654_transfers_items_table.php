<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_TransferItems', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('TransferId')->constrained('t_Transfers', 'Id')->onDelete('cascade');
            $table->foreignId('Item')->constrained('t_Items', 'Id');
            $table->integer('ApprovedQty');
            $table->integer('DispatchedQty');
            $table->string('Remarks')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_TransferItems');
    }
};
