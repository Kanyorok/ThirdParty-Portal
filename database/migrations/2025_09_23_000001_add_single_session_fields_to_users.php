<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_Users', static function (Blueprint $table) {
            if (!Schema::hasColumn('t_Users', 'current_session_id')) {
                $table->string('current_session_id', 255)->nullable()->index();
            }
            if (!Schema::hasColumn('t_Users', 'session_version')) {
                $table->unsignedBigInteger('session_version')->default(1)->index();
            }
            if (!Schema::hasColumn('t_Users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_Users', static function (Blueprint $table) {
            if (Schema::hasColumn('t_Users', 'current_session_id')) {
                $table->dropColumn('current_session_id');
            }
            if (Schema::hasColumn('t_Users', 'session_version')) {
                $table->dropColumn('session_version');
            }
            if (Schema::hasColumn('t_Users', 'last_login_at')) {
                $table->dropColumn('last_login_at');
            }
        });
    }
};


