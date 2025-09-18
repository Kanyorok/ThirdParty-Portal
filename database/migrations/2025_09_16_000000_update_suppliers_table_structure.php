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
        Schema::table('t_Suppliers', function (Blueprint $table) {
            // Add SupplierCategoryID to link to t_SupplierCategories
            // Names will come from t_ThirdParties via ThirdPartyID relationship
            if (!Schema::hasColumn('t_Suppliers', 'SupplierCategoryID')) {
                $table->foreignId('SupplierCategoryID')->nullable()->after('Active_Status')->constrained('t_SupplierCategories', 'SupplierCategoryID');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('t_Suppliers', 'SupplierCategoryID')) {
                $table->dropForeign(['SupplierCategoryID']);
                $table->dropColumn('SupplierCategoryID');
            }
        });
    }
};
