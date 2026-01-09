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
        if (Schema::hasColumn('t_Reports', 'PermissionName')) {
            return;
        }
        Schema::table('t_Reports', static function (Blueprint $table) {
            $table->string('PermissionName')->nullable()->after('Path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Reports', static function (Blueprint $table) {
            $table->dropColumn('PermissionName');
        });
    }
};
