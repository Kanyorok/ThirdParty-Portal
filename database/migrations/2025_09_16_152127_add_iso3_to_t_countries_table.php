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
        Schema::table('t_Countries', function (Blueprint $table) {
            $table->string('Iso3', 3)->nullable()->after('CountryCode')->comment('ISO 3166-1 alpha-3 country code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Countries', function (Blueprint $table) {
            $table->dropColumn('Iso3');
        });
    }
};