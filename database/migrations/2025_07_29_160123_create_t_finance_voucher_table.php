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
        Schema::create('t_FinanceVoucher', function (Blueprint $table) {
            $table->id('Id');
            $table->string('VoucherNo')->unique();
            $table->foreignId('InvoiceNo')->constrained('t_FinanceInvoiceEntry','Id');
            $table->decimal('TotalAmount', 10, 2);
            $table->string('PaymentMethod');
            $table->string('PaymentType')->default('Full');
            $table->date('StartDate')->nullable();
            $table->string('Frequency')->nullable();
            $table->enum('ApprovalStatus', ['draft', 'posted','rejected'])->default('draft');
            $table->text('ApprovalReason')->nullable();
            $table->text('Description');
            $table->text('Reasons')->nullable();

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FinanceVoucher');
    }
};
