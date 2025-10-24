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
        Schema::table('t_TenderCommitteeEvaluations', function (Blueprint $table) {
            // Add Score field if it doesn't exist
            if (!Schema::hasColumn('t_TenderCommitteeEvaluations', 'Score')) {
                $table->decimal('Score', 4, 2)->nullable()->after('CriteriaID')
                    ->comment('Individual criteria score (0-10 points)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_TenderCommitteeEvaluations', function (Blueprint $table) {
            if (Schema::hasColumn('t_TenderCommitteeEvaluations', 'Score')) {
                $table->dropColumn('Score');
            }
        });
    }
};
