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
        Schema::create('t_FinanceReceipts', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('ReceiptNumber', 50)->unique();
            $table->bigInteger('CustomerID');
            $table->date('ReceiptDate');
            $table->decimal('AmountReceived', 15);
            $table->string('PaymentMethod', 50);
            $table->string('ReferenceNumber', 100)->nullable();
            $table->date('ValueDate');
            $table->date('PostingDate');
            $table->string('AttachmentPath')->nullable();
            $table->text('Remarks')->nullable();
            $table->enum('Status', ['Draft', 'Posted'])->default('Draft');
            $table->string('ApprovalReason')->nullable();
            $table->decimal('UnappliedAmount', 15)->default(0);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec07f6cb3666');
            $table->index(['CustomerID', 'ReceiptDate']);
            $table->index(['Status', 'PostingDate']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FinanceReceipts');
    }
};
