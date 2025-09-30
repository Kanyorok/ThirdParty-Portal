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
        Schema::table('t_InsuranceProviders', function (Blueprint $table) {
            $table->dropColumn('Country');
            $table->foreignId('Country')->constrained('t_Countries', 'Id');
        });
        Schema::table('t_InsuranceProducts', function (Blueprint $table) {
            $table->dropColumn('Type');
            $table->foreignId('Type')->constrained('t_CodeDetails', 'ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_InsuranceProviders', function (Blueprint $table) {
            $table->dropForeign(['Country']);
            $table->dropColumn('Country');
            $table->string('Country');
        });

        Schema::table('t_InsuranceProducts', function (Blueprint $table) {
            $table->dropForeign(['Type']);
            $table->dropColumn('Type');
            $table->string('Type');
        });
    }
};
