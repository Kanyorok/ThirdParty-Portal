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
        Schema::table('t_TenderCommitteeEvaluations', function (Blueprint $table) {
            // Add missing columns that the model expects
            if (!Schema::hasColumn('t_TenderCommitteeEvaluations', 'IsActive')) {
                $table->boolean('IsActive')->default(true)->comment('Whether this evaluation record is active');
            }
            if (!Schema::hasColumn('t_TenderCommitteeEvaluations', 'MaxScore')) {
                $table->decimal('MaxScore', 4, 2)->default(10.00)->comment('Maximum possible score for this criteria');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_TenderCommitteeEvaluations', function (Blueprint $table) {
            if (Schema::hasColumn('t_TenderCommitteeEvaluations', 'IsActive')) {
                $table->dropColumn('IsActive');
            }
            if (Schema::hasColumn('t_TenderCommitteeEvaluations', 'MaxScore')) {
                $table->dropColumn('MaxScore');
            }
        });
    }
};
