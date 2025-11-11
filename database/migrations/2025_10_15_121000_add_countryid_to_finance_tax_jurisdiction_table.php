<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('t_FinanceTaxJurisdiction') && !Schema::hasColumn('t_FinanceTaxJurisdiction', 'CountryID')) {
            Schema::table('t_FinanceTaxJurisdiction', function (Blueprint $table) {
                if (Schema::hasTable('t_Countries')) {
                    $table->foreignId('CountryID')->nullable()->after('Currency')->constrained('t_Countries', 'Id');
                } else {
                    $table->unsignedBigInteger('CountryID')->nullable()->after('Currency');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('t_FinanceTaxJurisdiction') && Schema::hasColumn('t_FinanceTaxJurisdiction', 'CountryID')) {
            Schema::table('t_FinanceTaxJurisdiction', function (Blueprint $table) {
                $table->dropColumn('CountryID');
            });
        }
    }
};

