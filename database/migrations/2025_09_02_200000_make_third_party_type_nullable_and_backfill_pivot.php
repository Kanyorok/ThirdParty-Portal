<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Make legacy column nullable for backward compatibility
        Schema::table('t_ThirdParties', function (Blueprint $table) {
            if (Schema::hasColumn('t_ThirdParties', 'ThirdPartyType')) {
                $table->string('ThirdPartyType')->nullable()->change();
            }
        });

        // Backfill pivot for any existing records that still only have legacy value but no pivot rows.
        $thirdParties = DB::table('t_ThirdParties')->select('Id', 'ThirdPartyType')->whereNotNull('ThirdPartyType')->get();
        foreach ($thirdParties as $tp) {
            // Skip if pivot already exists
            $exists = DB::table('t_ThirdPartyType_ThirdParties')
                ->where('ThirdPartyId', $tp->Id)
                ->exists();
            if ($exists) {
                continue;
            }
            // Ensure value is integer; legacy enum may have stored numeric TypeId already; if not, skip
            if (!is_numeric($tp->ThirdPartyType)) {
                continue; // cannot map string enum code (S/T) to new TypeId without a mapping table
            }
            DB::table('t_ThirdPartyType_ThirdParties')->insert([
                'TypeId' => (int)$tp->ThirdPartyType,
                'ThirdPartyId' => $tp->Id,
                'CreatedOn' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // No data rollback for pivot inserts; only revert nullability if desired
        Schema::table('t_ThirdParties', function (Blueprint $table) {
            if (Schema::hasColumn('t_ThirdParties', 'ThirdPartyType')) {
                $table->string('ThirdPartyType')->nullable(false)->change();
            }
        });
    }
};
