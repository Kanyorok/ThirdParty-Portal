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
        Schema::table('t_ApplicationCategoryStatus', function (Blueprint $table) {
            $table->foreign(['ApplicationId'], 'FK_AppCatStatus_Application')->references(['ApplicationID'])->on('t_SupplierPrequalificationApplications')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['CategoryId'], 'FK_AppCatStatus_Category')->references(['SupplierCategoryID'])->on('t_SupplierCategories')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'], 'FK_AppCatStatus_CreatedBy')->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'], 'FK_AppCatStatus_DeletedBy')->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'], 'FK_AppCatStatus_ModifiedBy')->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ApplicationCategoryStatus', function (Blueprint $table) {
            $table->dropForeign('FK_AppCatStatus_Application');
            $table->dropForeign('FK_AppCatStatus_Category');
            $table->dropForeign('FK_AppCatStatus_CreatedBy');
            $table->dropForeign('FK_AppCatStatus_DeletedBy');
            $table->dropForeign('FK_AppCatStatus_ModifiedBy');
        });
    }
};
