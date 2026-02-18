<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HRExitClearances', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRExitClearances', 'Details')) {
                $table->text('Details')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRExitClearances', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRExitClearances', 'Details')) {
                $table->dropColumn('Details');
            }
        });
    }
};
