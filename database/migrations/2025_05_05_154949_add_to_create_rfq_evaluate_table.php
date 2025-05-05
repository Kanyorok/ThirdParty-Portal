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
        Schema::table('t_RFQEvaluation', function (Blueprint $table) {
            $table->foreignId('SupplierId')
                  ->after('RFQId') // Optional: places the column right after RFQId
                  ->constrained('t_Suppliers', 'Id')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RFQEvaluation', function (Blueprint $table) {
            $table->dropForeign(['SupplierId']);
            $table->dropColumn('SupplierId');
        });
    }
};
