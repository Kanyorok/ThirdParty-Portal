<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('t_SupplierPrequalificationApplications')) {
            try {
                Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
                    $table->dropUnique('uq_supplier_round');
                });
            } catch (\Throwable $e) {
                // ignore missing old index
            }
            try {
                Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
                    $table->unique(['CreatedBy', 'RoundID'], 'uq_user_round');
                });
            } catch (\Throwable $e) {
                // already exists
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('t_SupplierPrequalificationApplications')) {
            try {
                Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
                    $table->dropUnique('uq_user_round');
                });
            } catch (\Throwable $e) {
                // ignore
            }
            try {
                Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
                    $table->unique(['SupplierID', 'RoundID'], 'uq_supplier_round');
                });
            } catch (\Throwable $e) {
                // ignore existing
            }
        }
    }
};
