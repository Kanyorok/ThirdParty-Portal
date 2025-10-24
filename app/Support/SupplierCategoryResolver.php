<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SupplierCategoryResolver
{
    /**
     * Fetch supplier category IDs for a given third party, adapting to column-name differences.
     */
    public static function getCategoryIdsForThirdParty(int $thirdPartyId): array
    {
        $table = 't_ThirdParty_SupplierCategory';

        // Prefer snake_case (observed in both DBs), but be resilient.
        $thirdCol = collect(['third_party_id','ThirdPartyID','ThirdPartyId','thirdparty_id'])
            ->first(fn($c) => Schema::hasColumn($table, $c));
        $catCol = collect(['supplier_category_id','SupplierCategoryID','SupplierCategoryId','CategoryID','category_id'])
            ->first(fn($c) => Schema::hasColumn($table, $c));

        if (!$thirdCol || !$catCol) {
            Log::warning('SupplierCategoryResolver: unrecognized schema', [
                'table' => $table,
                'columns' => Schema::getColumnListing($table),
            ]);
            return [];
        }

        try {
            return DB::table($table)
                ->where($thirdCol, $thirdPartyId)
                ->pluck($catCol)
                ->filter(fn($v) => $v !== null && $v !== '')
                ->map(fn($v) => (int) $v)
                ->unique()
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::warning('Failed reading t_ThirdParty_SupplierCategory', [
                'thirdPartyId' => $thirdPartyId,
                'thirdCol' => $thirdCol,
                'catCol' => $catCol,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
}
