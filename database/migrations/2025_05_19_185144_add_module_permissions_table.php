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
        \Illuminate\Support\Facades\DB::table(config('permission.table_names.model_has_permissions'))->delete();
        \Illuminate\Support\Facades\DB::table(config('permission.table_names.role_has_permissions'))->delete();
        \Illuminate\Support\Facades\DB::table(config('permission.table_names.permissions'))->delete();
        Schema::table(config('permission.table_names.permissions'), static function (Blueprint $table) {
            $table->foreignId('ModuleId')->after('name')->constrained('t_Modules', 'ModuleID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('permission.table_names.permissions'), static function (Blueprint $table) {
            $table->dropConstrainedForeignId('ModuleId');
        });
    }
};
