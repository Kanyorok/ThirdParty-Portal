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
        Schema::table('t_FinanceInvoiceEntry', function (Blueprint $table) {
            if (!Schema::hasColumn('t_FinanceInvoiceEntry', 'InvoiceSourceType')) {
                $table->string('InvoiceSourceType', 20)->default('PO');
            }
            if (!Schema::hasColumn('t_FinanceInvoiceEntry', 'ContractSourceType')) {
                $table->string('ContractSourceType', 20)->nullable();
            }
            if (!Schema::hasColumn('t_FinanceInvoiceEntry', 'ContractSourceID')) {
                $table->unsignedBigInteger('ContractSourceID')->nullable();
            }
            if (!Schema::hasColumn('t_FinanceInvoiceEntry', 'MilestoneEligibilityStatus')) {
                $table->string('MilestoneEligibilityStatus', 20)->nullable();
            }
            if (!Schema::hasColumn('t_FinanceInvoiceEntry', 'IsOnHold')) {
                $table->boolean('IsOnHold')->default(false);
            }
            if (!Schema::hasColumn('t_FinanceInvoiceEntry', 'HoldReason')) {
                $table->text('HoldReason')->nullable();
            }
            if (!Schema::hasColumn('t_FinanceInvoiceEntry', 'HoldSetBy')) {
                $table->unsignedBigInteger('HoldSetBy')->nullable();
            }
            if (!Schema::hasColumn('t_FinanceInvoiceEntry', 'HoldSetOn')) {
                $table->dateTime('HoldSetOn')->nullable();
            }
            if (!Schema::hasColumn('t_FinanceInvoiceEntry', 'PenaltySuggestedAmount')) {
                $table->decimal('PenaltySuggestedAmount', 18, 2)->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceInvoiceEntry', function (Blueprint $table) {
            foreach ([
                'PenaltySuggestedAmount',
                'HoldSetOn',
                'HoldSetBy',
                'HoldReason',
                'IsOnHold',
                'MilestoneEligibilityStatus',
                'ContractSourceID',
                'ContractSourceType',
                'InvoiceSourceType',
            ] as $column) {
                if (Schema::hasColumn('t_FinanceInvoiceEntry', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

