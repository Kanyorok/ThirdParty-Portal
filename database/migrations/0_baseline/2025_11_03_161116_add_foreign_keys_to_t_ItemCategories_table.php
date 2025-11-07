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
        Schema::table('t_ItemCategories', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ParentId'])->references(['Id'])->on('t_ItemCategories')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Status'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ItemCategories', function (Blueprint $table) {
            $table->dropForeign('t_itemcategories_createdby_foreign');
            $table->dropForeign('t_itemcategories_deletedby_foreign');
            $table->dropForeign('t_itemcategories_modifiedby_foreign');
            $table->dropForeign('t_itemcategories_parentid_foreign');
            $table->dropForeign('t_itemcategories_status_foreign');
        });
    }
};
