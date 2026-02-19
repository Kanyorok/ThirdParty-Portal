<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HRStatutoryReliefs', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRStatutoryReliefs', 'ReliefType')) {
                $table->string('ReliefType', 20)->default('Fixed')->after('Amount');
            }
            if (!Schema::hasColumn('t_HRStatutoryReliefs', 'ReliefRate')) {
                $table->decimal('ReliefRate', 9, 4)->nullable()->after('ReliefType');
            }
            if (!Schema::hasColumn('t_HRStatutoryReliefs', 'DeductionID')) {
                $table->unsignedBigInteger('DeductionID')->nullable()->after('ReliefRate');
            }
            if (!Schema::hasColumn('t_HRStatutoryReliefs', 'ApplyStage')) {
                $table->string('ApplyStage', 20)->default('PostTax')->after('DeductionID');
            }
        });

        DB::table('t_HRStatutoryReliefs')
            ->whereNull('ReliefType')
            ->update(['ReliefType' => 'Fixed']);
        DB::table('t_HRStatutoryReliefs')
            ->whereNull('ApplyStage')
            ->update(['ApplyStage' => 'PostTax']);
    }

    public function down(): void
    {
        Schema::table('t_HRStatutoryReliefs', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRStatutoryReliefs', 'ApplyStage')) {
                $table->dropColumn('ApplyStage');
            }
            if (Schema::hasColumn('t_HRStatutoryReliefs', 'DeductionID')) {
                $table->dropColumn('DeductionID');
            }
            if (Schema::hasColumn('t_HRStatutoryReliefs', 'ReliefRate')) {
                $table->dropColumn('ReliefRate');
            }
            if (Schema::hasColumn('t_HRStatutoryReliefs', 'ReliefType')) {
                $table->dropColumn('ReliefType');
            }
        });
    }
};
