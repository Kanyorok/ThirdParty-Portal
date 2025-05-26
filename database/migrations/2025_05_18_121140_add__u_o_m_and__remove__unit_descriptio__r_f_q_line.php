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
        Schema::table('t_RFQLines', function (Blueprint $table) {

            // Drop RequisitionItems JSON column
            $table->dropColumn('UnitDescription');

            // Add new RequisitionId column
            $table->string('UOM'); // adjust table name if different
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RFQLines', function (Blueprint $table) {
            $table->string('UnitDescription');
            
             $table->dropColumn('UOM');// match your original constraint
        });
    }
};
