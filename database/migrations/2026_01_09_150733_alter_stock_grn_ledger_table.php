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
        Schema::table('t_StockGRNLedger', function (Blueprint $table) {
            $table->string('SourceType', 50)->nullable();
            $table->string('SourceReference', 100)->nullable();
            $table->foreignId('ParentLedgerId')->nullable()->constrained('t_StockGRNLedger', 'Id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_StockGRNLedger', function (Blueprint $table) {
            $table->dropColumn('SourceType');
            $table->dropColumn('SourceReference');
            $table->dropForeign(['ParentLedgerId']);
            $table->dropColumn('ParentLedgerId');
        });
        //
    }
};
