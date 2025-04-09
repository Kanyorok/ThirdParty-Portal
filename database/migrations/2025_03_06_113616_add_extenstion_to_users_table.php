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
        Schema::table('t_Users', static function (Blueprint $table) {
            $table->string('ExtensionNo',50)->index()->nullable()->after('Phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Users', static function (Blueprint $table) {
            $table->dropIndex('t_Users_ExtensionNo_index');
            $table->dropColumn('ExtensionNo');
        });
    }
};
