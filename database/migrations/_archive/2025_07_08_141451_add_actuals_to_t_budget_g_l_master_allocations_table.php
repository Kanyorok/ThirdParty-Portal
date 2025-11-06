<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_BudgetGLMasterAllocations', function (Blueprint $table) {
            $table->decimal('Actuals', 15, 2)->default(0.00); // 15 total digits, 2 decimal places
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BudgetGLMasterAllocations', function (Blueprint $table) {
            $table->dropColumn('Actuals');
        });
    }
};
