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
            // Add only compatible columns used by the app
            if (!Schema::hasColumn('t_OrderLines', 'UnitOfMeasure')) {
                $table->string('UnitOfMeasure', 50)->nullable()->comment('Unit of measure (pcs, kg, liters, etc.)');
            }
            
            if (!Schema::hasColumn('t_OrderLines', 'TaxPercentage')) {
                $table->decimal('TaxPercentage', 5, 2)->default(0)->comment('Tax percentage for this line item');
            }
            
            if (!Schema::hasColumn('t_OrderLines', 'DiscountPercentage')) {
                $table->decimal('DiscountPercentage', 5, 2)->default(0)->comment('Discount percentage for this line item');
            }
            
            // Add indexes on actual columns if missing (safely ignore failures)
            try { $table->index('iOrderID', 't_OrderLines_iOrderID_index'); } catch (\Throwable $e) {}
            try { $table->index('iStockCodeID', 't_OrderLines_iStockCodeID_index'); } catch (\Throwable $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_OrderLines', function (Blueprint $table) {
            // Drop indexes (ignore if they do not exist)
            try { $table->dropIndex(['t_OrderLines_iOrderID_index']); } catch (\Throwable $e) {}
            try { $table->dropIndex(['t_OrderLines_iStockCodeID_index']); } catch (\Throwable $e) {}
            
            // Drop added columns if they exist
            $columnsToDrop = ['UnitOfMeasure', 'TaxPercentage', 'DiscountPercentage'];
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('t_OrderLines', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
