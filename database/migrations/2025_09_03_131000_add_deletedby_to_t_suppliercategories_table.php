<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_SupplierCategories', function (Blueprint $table) {
            if (!Schema::hasColumn('t_SupplierCategories', 'DeletedBy')) {
                $table->string('DeletedBy', 100)->nullable()->after('ModifiedOn');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_SupplierCategories', function (Blueprint $table) {
            if (Schema::hasColumn('t_SupplierCategories', 'DeletedBy')) {
                $table->dropColumn('DeletedBy');
            }
        });
    }
};
