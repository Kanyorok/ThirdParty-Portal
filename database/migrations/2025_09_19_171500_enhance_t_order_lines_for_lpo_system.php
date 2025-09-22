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
        Schema::table('t_OrderLines', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('t_OrderLines', 'ItemDescription')) {
                $table->text('ItemDescription')->nullable()->after('OrderID')
                      ->comment('Description of the item being ordered');
            }
            
            if (!Schema::hasColumn('t_OrderLines', 'UnitOfMeasure')) {
                $table->string('UnitOfMeasure', 50)->nullable()->after('Quantity')
                      ->comment('Unit of measure (pcs, kg, liters, etc.)');
            }
            
            if (!Schema::hasColumn('t_OrderLines', 'TaxPercentage')) {
                $table->decimal('TaxPercentage', 5, 2)->default(0)->after('UnitPrice')
                      ->comment('Tax percentage for this line item');
            }
            
            if (!Schema::hasColumn('t_OrderLines', 'DiscountPercentage')) {
                $table->decimal('DiscountPercentage', 5, 2)->default(0)->after('TaxPercentage')
                      ->comment('Discount percentage for this line item');
            }
            
            if (!Schema::hasColumn('t_OrderLines', 'TotalAmount')) {
                $table->decimal('TotalAmount', 15, 2)->nullable()->after('DiscountPercentage')
                      ->comment('Total amount for this line (including tax and discount)');
            }
            
            if (!Schema::hasColumn('t_OrderLines', 'Notes')) {
                $table->text('Notes')->nullable()->after('TotalAmount')
                      ->comment('Additional notes for this line item');
            }
            
            // Add indexes for better performance
            if (!Schema::hasIndex('t_OrderLines', ['OrderID'])) {
                $table->index('OrderID');
            }
            
            if (!Schema::hasIndex('t_OrderLines', ['ItemID'])) {
                $table->index('ItemID');
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
            $indexesToDrop = ['OrderID', 'ItemID'];
            foreach ($indexesToDrop as $index) {
                if (Schema::hasIndex('t_OrderLines', $index)) {
                    $table->dropIndex(["t_OrderLines_{$index}_index"]);
                }
            }
            
            // Drop columns if they exist
            $columnsToDrop = ['ItemDescription', 'UnitOfMeasure', 'TaxPercentage', 'DiscountPercentage', 'TotalAmount', 'Notes'];
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('t_OrderLines', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
