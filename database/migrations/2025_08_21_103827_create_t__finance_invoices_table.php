<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_FinanceInvoices', function (Blueprint $table) {
            $table->id('Id');
            $table->string('RequestID')->nullable();
            $table->string('IdempotencyKey', 100)->nullable()->unique();
            $table->integer('ModuleID')->nullable();
            $table->integer('CurrencyID')->nullable();
            $table->integer('CustomerID')->nullable();
            $table->string('InvoiceID', 100)->nullable();
            $table->string('InvoiceNumber', 100)->nullable();
            $table->string('InvoiceTitle', 100)->nullable();
            $table->date('InvoiceDate')->nullable();
            $table->date('DueDate')->nullable();
            $table->string('InvoiceRemarks', 100)->nullable();
            $table->integer('TotalAmount')->nullable();
            $table->integer('AmountPaid')->nullable();
            $table->boolean('IsPaid')->default(false);
            $table->string('SourceTable')->nullable();
            $table->boolean('IsGenerated')->default(false);
            $table->string('Status', 20)->default('draft'); //To be used in workflows
            $table->enum('ApprovalStatus', ['draft', 'posted', 'rejected'])->default('draft');
            $table->text('ApprovalReason')->nullable();

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
        Schema::dropIfExists('t_FinanceInvoices');
    }
};
