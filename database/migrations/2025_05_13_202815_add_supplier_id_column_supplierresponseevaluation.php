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
        Schema::table('t_SupplierResponseEvaluations', function (Blueprint $table) {
            $table->unsignedBigInteger('SupplierId')->nullable()->after('Id'); // Adjust position if needed
            $table->foreign('SupplierId')->references('Id')->on('t_Suppliers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_SupplierResponseEvaluations', function (Blueprint $table) {
            $table->dropForeign(['SupplierId']);
            $table->dropColumn('SupplierId');
        });
    }
};
