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
        Schema::table('t_ThirdPartiesCategories', function (Blueprint $table) {
            $table->foreign(['CategoryID'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ThirdPartyId'])->references(['Id'])->on('t_ThirdParties')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ThirdPartiesCategories', function (Blueprint $table) {
            $table->dropForeign('t_thirdpartiescategories_categoryid_foreign');
            $table->dropForeign('t_thirdpartiescategories_createdby_foreign');
            $table->dropForeign('t_thirdpartiescategories_deletedby_foreign');
            $table->dropForeign('t_thirdpartiescategories_modifiedby_foreign');
            $table->dropForeign('t_thirdpartiescategories_thirdpartyid_foreign');
        });
    }
};
