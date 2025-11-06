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
        Schema::table('t_ModelPermissions', function (Blueprint $table) {
            $table->foreign(['permission_id'])->references(['id'])->on('t_Permissions')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ModelPermissions', function (Blueprint $table) {
            $table->dropForeign('t_modelpermissions_permission_id_foreign');
        });
    }
};
