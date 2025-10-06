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
        Schema::table('t_Countries', function (Blueprint $table) {
            if (!Schema::hasColumn('t_Countries', 'IsActive')) {
                $table->tinyInteger('IsActive')->default(1)->after('Flag');
            }
            if (!Schema::hasColumn('t_Countries', 'SortOrder')) {
                $table->integer('SortOrder')->default(0)->after('IsActive');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Countries', function (Blueprint $table) {
            if (Schema::hasColumn('t_Countries', 'IsActive')) {
                $table->dropColumn('IsActive');
            }
            if (Schema::hasColumn('t_Countries', 'SortOrder')) {
                $table->dropColumn('SortOrder');
            }
        });
    }
};