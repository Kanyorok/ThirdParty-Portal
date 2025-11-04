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
            $table->bigIncrements('Id');
            $table->string('VoucherNo')->unique();
            $table->bigInteger('InvoiceNo');
            $table->decimal('TotalAmount', 10);
            $table->string('PaymentMethod');
            $table->string('PaymentType')->default('Full');
            $table->date('StartDate')->nullable();
            $table->date('EndDate')->nullable();
            $table->string('Frequency')->nullable();
            $table->enum('ApprovalStatus', ['draft', 'posted', 'rejected'])->default('draft');
            $table->text('ApprovalReason')->nullable();
            $table->string('Status')->default('draft');
            $table->boolean('IsProcessed')->default(false);
            $table->text('Description');
            $table->text('Reasons')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec077b9c213c');
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
