<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('t_SupplierPrequalificationApplications')) {
            // Attempt to create index; ignore if already exists
            try {
                Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
                    $table->unique(['SupplierID', 'RoundID'], 'uq_supplier_round');
                });
            } catch (\Throwable $e) {
                // Likely already exists; swallow error
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('t_SupplierPrequalificationApplications')) {
            try {
                Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
                    $table->dropUnique('uq_supplier_round');
                });
            } catch (\Throwable $e) {
                // Index missing; ignore
            }
        }
    }
};
