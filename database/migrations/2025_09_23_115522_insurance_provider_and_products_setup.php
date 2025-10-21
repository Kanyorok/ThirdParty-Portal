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
        /**
         * --- INSURANCE PROVIDERS ---
         */
        // Drop old Country column if it exists (constraint will go with it)
        if (Schema::hasColumn('t_InsuranceProviders', 'Country')) {
            Schema::table('t_InsuranceProviders', function (Blueprint $table) {
                $table->dropColumn('Country');
            });
        }

        // Add new Country column as nullable first
        Schema::table('t_InsuranceProviders', function (Blueprint $table) {
            $table->unsignedBigInteger('Country')->nullable()->after('Id');
        });

        // Fill existing rows with default value (1)
        DB::table('t_InsuranceProviders')->update(['Country' => 1]);

        // Alter column to not nullable + default + FK
        Schema::table('t_InsuranceProviders', function (Blueprint $table) {
            $table->unsignedBigInteger('Country')->default(1)->change();
            $table->foreign('Country')->references('Id')->on('t_Countries');
        });


        /**
         * --- INSURANCE PRODUCTS ---
         */
        // Drop old Type column if it exists (constraint will go with it)
        if (Schema::hasColumn('t_InsuranceProducts', 'Type')) {
            Schema::table('t_InsuranceProducts', function (Blueprint $table) {
                $table->dropColumn('Type');
            });
        }

        // Add new Type column as nullable first
        Schema::table('t_InsuranceProducts', function (Blueprint $table) {
            $table->unsignedBigInteger('Type')->nullable()->after('Id');
        });

        // Fill existing rows with default value (1)
        DB::table('t_InsuranceProducts')->update(['Type' => 1]);

        // Alter column to not nullable + default + FK
        Schema::table('t_InsuranceProducts', function (Blueprint $table) {
            $table->unsignedBigInteger('Type')->default(1)->change();
            $table->foreign('Type')->references('ID')->on('t_CodeDetails');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /**
         * --- INSURANCE PROVIDERS ---
         */
        Schema::table('t_InsuranceProviders', function (Blueprint $table) {
            // Drop new FK + column
            if (Schema::hasColumn('t_InsuranceProviders', 'Country')) {
                $table->dropForeign(['Country']);
                $table->dropColumn('Country');
            }

            // Recreate old Country column (string, nullable for safety)
            $table->string('Country')->nullable();
        });

        /**
         * --- INSURANCE PRODUCTS ---
         */
        Schema::table('t_InsuranceProducts', function (Blueprint $table) {
            // Drop new FK + column
            if (Schema::hasColumn('t_InsuranceProducts', 'Type')) {
                $table->dropForeign(['Type']);
                $table->dropColumn('Type');
            }

            // Recreate old Type column (string, nullable for safety)
            $table->string('Type')->nullable();
        });
    }
};
