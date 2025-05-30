<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_RFQEvaluations', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['SupplierId']);

            // Then drop the column
            $table->dropColumn('SupplierId');
        });
    }

    public function down(): void
    {
        Schema::table('t_RFQEvaluations', function (Blueprint $table) {
            // Add the column back
            $table->unsignedBigInteger('SupplierId')->nullable();

            // Recreate the foreign key (if needed)
            $table->foreign('SupplierId')->references('Id')->on('t_Suppliers');
        });
    }
};
