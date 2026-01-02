<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_Pricing', function (Blueprint $table) {
            // First, drop the existing foreign key constraints
            $table->dropForeign(['UOM']);
            
            // Drop the existing columns
            $table->dropColumn('UOM');
        });

        Schema::table('t_Pricing', function (Blueprint $table) {
            // Re-add the columns with new foreign key constraints
            $table->foreignId('UOM')->nullable()->constrained('t_UOM','Id');
        });
    }

    public function down(): void
    {
        Schema::table('t_Pricing', function (Blueprint $table) {
            // Drop the new foreign key constraints
            $table->dropForeign(['UOM']);
            
            // Drop the columns
            $table->dropColumn('UOM');
        });

        Schema::table('t_Pricing', function (Blueprint $table) {
            // Revert back to the original structure with t_CodeDetails
         $table->foreignId('UOM')->constrained('t_Items', 'Id');


        });
    }
};