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
        Schema::table('t_ComplianceTrainingSessions', function (Blueprint $table) {
            $table->foreign(['TrainingTypeID'])->references(['Id'])->on('t_TrainingTypes')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ComplianceTrainingSessions', function (Blueprint $table) {
            $table->dropForeign('t_compliancetrainingsessions_trainingtypeid_foreign');
        });
    }
};
