<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if Direct Purchase method exists (ID 106)
        $directPurchase = DB::table('t_CodeDetails')
            ->where('CodeID', 'ProcurementMethod')
            ->where('ID', 106)
            ->first();
            
        if ($directPurchase) {
            echo "Direct Purchase method already exists with ID: 106 - {$directPurchase->Description}\n";
            
            // Update any plan items using method 107 (Tender) to use 106 (Direct Purchase) for testing
            $updated = DB::table('t_PlanLineItem')
                ->where('ProcurementMethod', 107)
                ->update([
                    'ProcurementMethod' => 106, // Use Direct Purchase
                    'ModifiedBy' => 1,
                    'ModifiedOn' => now(),
                ]);
                
            echo "Updated {$updated} plan items to use Direct Purchase method (ID: 106)\n";
        } else {
            echo "Direct Purchase method (ID: 106) not found\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert plan items back to method 107 if needed
        DB::table('t_PlanLineItem')
            ->where('ProcurementMethod', 106)
            ->update([
                'ProcurementMethod' => 107,
                'ModifiedBy' => 1,
                'ModifiedOn' => now(),
            ]);
    }
};