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
        Schema::table('t_OrderLines', function (Blueprint $table) {
            // Add new fields that don't exist
            if (!Schema::hasColumn('t_OrderLines', 'TaxPercentage')) {
                $table->decimal('TaxPercentage', 5, 2)->default(0)->after('fTaxRate')
                    ->comment('Tax percentage for this line item (new LPO field)');
            }

            if (!Schema::hasColumn('t_OrderLines', 'DiscountPercentage')) {
                $table->decimal('DiscountPercentage', 5, 2)->default(0)->after('TaxPercentage')
                    ->comment('Discount percentage for this line item (new LPO field)');
            }

            if (!Schema::hasColumn('t_OrderLines', 'UnitOfMeasure')) {
                $table->string('UnitOfMeasure', 50)->nullable()->after('DiscountPercentage')
                    ->comment('Unit of measure for LPO items');
            }

            // Add indexes for better performance on existing columns
            if (!$this->indexExists('t_OrderLines', 'iOrderID')) {
                $table->index('iOrderID', 't_orderlines_iorderid_index');
            }

            if (!$this->indexExists('t_OrderLines', 'iStockCodeID')) {
                $table->index('iStockCodeID', 't_orderlines_istockcodeid_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_OrderLines', function (Blueprint $table) {
            // Drop indexes first
            if ($this->indexExists('t_OrderLines', 'iOrderID')) {
                $table->dropIndex('t_orderlines_iorderid_index');
            }

            if ($this->indexExists('t_OrderLines', 'iStockCodeID')) {
                $table->dropIndex('t_orderlines_istockcodeid_index');
            }

            // Drop columns if they exist
            $columnsToDrop = ['TaxPercentage', 'DiscountPercentage', 'UnitOfMeasure'];
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('t_OrderLines', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $column): bool
    {
        try {
            $connection = Schema::connection('sqlsrv');
            $indexes = $connection->getConnection()->select("
                SELECT i.name as index_name, c.name as column_name
                FROM sys.indexes i
                JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
                JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
                JOIN sys.tables t ON i.object_id = t.object_id
                WHERE t.name = ? AND c.name = ?
            ", [$table, $column]);

            return count($indexes) > 0;
        } catch (Exception $e) {
            return false;
        }
    }
};
