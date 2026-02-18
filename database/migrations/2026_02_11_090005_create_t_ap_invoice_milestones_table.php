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
        Schema::create('t_APInvoiceMilestones', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('FinanceInvoiceID')
                ->constrained('t_FinanceInvoiceEntry', 'Id')
                ->cascadeOnDelete();
            $table->foreignId('MilestoneID')
                ->constrained('t_ContractMilestones', 'Id')
                ->cascadeOnDelete();
            $table->decimal('BilledAmount', 18, 2)->default(0);
            $table->timestamps();

            $table->unique(['FinanceInvoiceID', 'MilestoneID'], 'uq_ap_invoice_milestones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_APInvoiceMilestones');
    }
};

