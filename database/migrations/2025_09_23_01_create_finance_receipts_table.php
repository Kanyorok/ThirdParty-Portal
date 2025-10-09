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
            $table->id('Id');
            $table->string('ReceiptNumber', 50)->unique();
            $table->foreignId('CustomerID')->constrained('t_ThirdParties', 'Id');
            $table->date('ReceiptDate');
            $table->decimal('AmountReceived', 15, 2);
            $table->string('PaymentMethod', 50);
            $table->string('ReferenceNumber', 100)->nullable();
            $table->date('ValueDate');
            $table->date('PostingDate');
            $table->string('AttachmentPath')->nullable();
            $table->text('Remarks')->nullable();
            $table->enum('Status', ['Draft', 'Posted'])->default('Draft');
            $table->string('ApprovalReason')->nullable();
            $table->decimal('UnappliedAmount', 15, 2)->default(0); // For wallet/excess amount
            
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
            
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
