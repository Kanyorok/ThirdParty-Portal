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
        Schema::table('t_Permissions', function (Blueprint $table) {
            $table->foreign(['ModuleId'])->references(['ModuleID'])->on('t_Modules')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Permissions', function (Blueprint $table) {
            $table->dropForeign('t_permissions_moduleid_foreign');
        });
    }
};
