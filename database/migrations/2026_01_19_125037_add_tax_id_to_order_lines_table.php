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
        Schema::table('t_OrderLines', function (Blueprint $table) {
            if (!Schema::hasColumn('t_OrderLines', 'TaxID')) {
                $table->foreignId('TaxID')->nullable()->constrained('t_FinanceTaxRuleConfiguration', 'Id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_OrderLines', function (Blueprint $table) {
            if (Schema::hasColumn('t_OrderLines', 'TaxID')) {
                // Check if foreign key exists before dropping - strict check might require raw SQL
                // But generally dropForeign accepts array of columns
               // $table->dropForeign(['TaxID']); // Might fail if FK doesn't exist but column does
                $table->dropColumn('TaxID');
            }
        });
    }
};
