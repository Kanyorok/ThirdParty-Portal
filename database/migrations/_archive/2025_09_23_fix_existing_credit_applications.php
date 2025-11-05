<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing invoices that have credit movements but UseCredit is false
        $invoicesWithCreditMovements = DB::table('t_FinanceCreditMovements')
            ->where('ReferenceType', 'invoice')
            ->where('MovementType', 'invoice_usage')
            ->pluck('ReferenceID')
            ->unique();

        foreach ($invoicesWithCreditMovements as $invoiceId) {
            DB::table('t_FinanceInvoices')
                ->where('Id', $invoiceId)
                ->update([
                    'UseCredit' => true,
                    'CreditAppliedOn' => DB::raw('(SELECT TOP 1 EffectiveOn FROM t_FinanceCreditMovements WHERE ReferenceType = \'invoice\' AND ReferenceID = ' . $invoiceId . ')'),
                    'CreditAppliedBy' => DB::raw('(SELECT TOP 1 CreatedBy FROM t_FinanceCreditMovements WHERE ReferenceType = \'invoice\' AND ReferenceID = ' . $invoiceId . ')'),
                    'CreditApplicationReason' => 'Applied via credit management (retroactive update)',
                ]);
        }

        echo "Updated " . count($invoicesWithCreditMovements) . " invoices with credit movements\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset all UseCredit fields
        DB::table('t_FinanceInvoices')->update([
            'UseCredit' => false,
            'CreditAppliedOn' => null,
            'CreditAppliedBy' => null,
            'CreditApplicationReason' => null,
        ]);
    }
};
