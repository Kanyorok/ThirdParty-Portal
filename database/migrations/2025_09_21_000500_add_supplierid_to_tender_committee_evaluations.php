<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('t_TenderCommitteeEvaluations')) {
            Schema::table('t_TenderCommitteeEvaluations', function (Blueprint $table) {
                if (!Schema::hasColumn('t_TenderCommitteeEvaluations', 'SupplierId')) {
                    $table->integer('SupplierId')->nullable()->index();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('t_TenderCommitteeEvaluations')) {
            Schema::table('t_TenderCommitteeEvaluations', function (Blueprint $table) {
                if (Schema::hasColumn('t_TenderCommitteeEvaluations', 'SupplierId')) {
                    $table->dropColumn('SupplierId');
                }
            });
        }
    }
};



