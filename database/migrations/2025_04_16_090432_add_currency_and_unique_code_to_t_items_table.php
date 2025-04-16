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
        // Step 1: Add columns without unique constraint
        Schema::table('t_Items', function (Blueprint $table) {
            $table->string('Currency', 10)->nullable()->after('UnitPrice')->comment('Currency of the item');
            $table->string('UniqueCode', 50)->nullable()->after('Currency')->comment('Unique code for the item');
        });

        // Step 2: Generate and assign unique codes
        $goods = DB::table('t_Items')->where('Type', 'good')->whereNull('UniqueCode')->get();
        $services = DB::table('t_Items')->where('Type', 'service')->whereNull('UniqueCode')->get();

        $counterGood = 1;
        foreach ($goods as $item) {
            $code = 'IT' . str_pad($counterGood, 3, '0', STR_PAD_LEFT);
            DB::table('t_Items')->where('id', $item->id)->update(['UniqueCode' => $code]);
            $counterGood++;
        }

        $counterService = 1;
        foreach ($services as $item) {
            $code = 'SE' . str_pad($counterService, 3, '0', STR_PAD_LEFT);
            DB::table('t_Items')->where('id', $item->id)->update(['UniqueCode' => $code]);
            $counterService++;
        }

        // Step 3: Add unique index after data is populated
        Schema::table('t_Items', function (Blueprint $table) {
            $table->unique('UniqueCode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Items', function (Blueprint $table) {
            $table->dropUnique(['UniqueCode']);
            $table->dropColumn(['Currency', 'UniqueCode']);
        });
    }
};
