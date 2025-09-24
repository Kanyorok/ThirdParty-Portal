<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Update existing credit movements to use signed amounts:
     * - Positive amounts = increase available credit (payments, adjustments up, initial setup)
     * - Negative amounts = decrease available credit (invoice usage, adjustments down)
     */
    public function up(): void
    {
        // Update existing records to use signed amounts
        DB::statement("
            UPDATE t_FinanceCreditMovements 
            SET Amount = -Amount 
            WHERE MovementType IN ('invoice_usage', 'adjustment_decrease', 'usage')
            AND Amount > 0
        ");
        
        // Add comment to the Amount column to document the new approach
        DB::statement("
            EXEC sp_addextendedproperty 
            @name = N'MS_Description',
            @value = N'Signed amount: Positive = increases available credit, Negative = decreases available credit',
            @level0type = N'SCHEMA', @level0name = N'dbo',
            @level1type = N'TABLE', @level1name = N't_FinanceCreditMovements',
            @level2type = N'COLUMN', @level2name = N'Amount'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to positive amounts for all records
        DB::statement("
            UPDATE t_FinanceCreditMovements 
            SET Amount = ABS(Amount)
        ");
        
        // Remove the column comment
        DB::statement("
            EXEC sp_dropextendedproperty 
            @name = N'MS_Description',
            @level0type = N'SCHEMA', @level0name = N'dbo',
            @level1type = N'TABLE', @level1name = N't_FinanceCreditMovements',
            @level2type = N'COLUMN', @level2name = N'Amount'
        ");
    }
};
