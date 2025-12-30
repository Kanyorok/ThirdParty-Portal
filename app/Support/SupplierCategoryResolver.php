<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SupplierCategoryResolver
{
    /**
     * Fetch supplier category IDs for a given third party, adapting to column-name differences.
     * Checks both t_ThirdParty_SupplierCategory and t_PrequalificationRoundSupplierCategory tables.
     * 
     * @param int $thirdPartyId The ID from t_ThirdParties
     * @param int|null $supplierMasterId The ID from t_SupplierMaster (optional, for prequalification lookup)
     */
    public static function getCategoryIdsForThirdParty(int $thirdPartyId, ?int $supplierMasterId = null): array
    {
        $categoryIds = collect();

        // 1. Check t_ThirdParty_SupplierCategory table (uses t_ThirdParties.Id)
        $table1 = 't_ThirdParty_SupplierCategory';
        if (Schema::hasTable($table1)) {
            $thirdCol = collect(['third_party_id','ThirdPartyID','ThirdPartyId','thirdparty_id'])
                ->first(fn($c) => Schema::hasColumn($table1, $c));
            $catCol = collect(['supplier_category_id','SupplierCategoryID','SupplierCategoryId','CategoryID','category_id'])
                ->first(fn($c) => Schema::hasColumn($table1, $c));

            if ($thirdCol && $catCol) {
                try {
                    $cats = DB::table($table1)
                        ->where($thirdCol, $thirdPartyId)
                        ->pluck($catCol)
                        ->filter(fn($v) => $v !== null && $v !== '')
                        ->map(fn($v) => (int) $v);
                    
                    $categoryIds = $categoryIds->merge($cats);
                    Log::info("Found {$cats->count()} categories from {$table1} for ThirdPartyId {$thirdPartyId}");
                } catch (\Throwable $e) {
                    Log::warning("Failed reading {$table1}", [
                        'thirdPartyId' => $thirdPartyId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // 2. Check t_PrequalificationRoundSupplierCategory table (uses t_SupplierMaster.Id)
        $table2 = 't_PrequalificationRoundSupplierCategory';
        if (Schema::hasTable($table2) && $supplierMasterId !== null) {
            $thirdCol = collect(['ThirdPartyID','third_party_id','ThirdPartyId','thirdparty_id'])
                ->first(fn($c) => Schema::hasColumn($table2, $c));
            $catCol = collect(['SupplierCategoryID','supplier_category_id','SupplierCategoryId','CategoryID','category_id'])
                ->first(fn($c) => Schema::hasColumn($table2, $c));

            if ($thirdCol && $catCol) {
                try {
                    // CRITICAL: Use SupplierMaster.Id, not ThirdParty.Id for this table
                    $cats = DB::table($table2)
                        ->where($thirdCol, $supplierMasterId)
                        ->pluck($catCol)
                        ->filter(fn($v) => $v !== null && $v !== '')
                        ->map(fn($v) => (int) $v);
                    
                    $categoryIds = $categoryIds->merge($cats);
                    Log::info("Found {$cats->count()} categories from {$table2} for SupplierMasterId {$supplierMasterId}");
                } catch (\Throwable $e) {
                    Log::warning("Failed reading {$table2}", [
                        'supplierMasterId' => $supplierMasterId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $result = $categoryIds
            ->unique()
            ->values()
            ->all();
        
        Log::info("Total unique supplier categories: " . count($result), [
            'thirdPartyId' => $thirdPartyId,
            'supplierMasterId' => $supplierMasterId,
            'categories' => $result
        ]);
        
        return $result;
    }
}
