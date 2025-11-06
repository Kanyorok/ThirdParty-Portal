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
        Schema::table('t_PrequalificationApplicationCategories', function (Blueprint $table) {
            $table->foreign(['ApplicationID'])->references(['ApplicationID'])->on('t_SupplierPrequalificationApplications')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['CategoryID'])->references(['SupplierCategoryID'])->on('t_SupplierCategories')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_PrequalificationApplicationCategories', function (Blueprint $table) {
            $table->dropForeign('t_prequalificationapplicationcategories_applicationid_foreign');
            $table->dropForeign('t_prequalificationapplicationcategories_categoryid_foreign');
        });
    }
};
