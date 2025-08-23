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
        if (Schema::hasTable('t_PrequalificationRoundCriteria') && !Schema::hasColumn('t_PrequalificationRoundCriteria', 'Weight')) {
            Schema::table('t_PrequalificationRoundCriteria', function (Blueprint $table) {
                $table->integer('Weight')->default(0)->after('Id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('t_PrequalificationRoundCriteria') && Schema::hasColumn('t_PrequalificationRoundCriteria', 'Weight')) {
            Schema::table('t_PrequalificationRoundCriteria', function (Blueprint $table) {
                $table->dropColumn('Weight');
            });
        }
    }
};
