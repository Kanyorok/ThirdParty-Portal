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
        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            $table->foreign(['CategoryID'])->references(['SupplierCategoryID'])->on('t_SupplierCategories')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['RoundID'])->references(['RoundID'])->on('t_PrequalificationRounds')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['SupplierID'])->references(['Id'])->on('t_ThirdParties')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            $table->dropForeign('t_supplierprequalificationapplications_categoryid_foreign');
            $table->dropForeign('t_supplierprequalificationapplications_createdby_foreign');
            $table->dropForeign('t_supplierprequalificationapplications_deletedby_foreign');
            $table->dropForeign('t_supplierprequalificationapplications_modifiedby_foreign');
            $table->dropForeign('t_supplierprequalificationapplications_roundid_foreign');
            $table->dropForeign('t_supplierprequalificationapplications_supplierid_foreign');
        });
    }
};
