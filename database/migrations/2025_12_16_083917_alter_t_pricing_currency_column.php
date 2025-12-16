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
        Schema::table('t_Pricing', function (Blueprint $table) {
            $table->dropColumn('CurrencyCode');
        });
        Schema::table('t_Pricing', function (Blueprint $table) {
            $table->foreignId('CurrencyCode')->nullable()->constrained('t_Currencies', 'Id')->change();
            $table->decimal('ActualPrice', 18, 2)->nullable()->change();

        });




    }
        //
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Pricing', function (Blueprint $table) {
            $table->dropForeign(['CurrencyCode']);
            $table->dropColumn('CurrencyCode');
            $table->string('CurrencyCode', 3)->nullable();
            $table->decimal('ActualPrice')->nullable()->change();
         });
        //
    }
};
