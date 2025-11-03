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
        Schema::table('t_ThirdParties', function (Blueprint $table) {
            $table->foreign(['CountryId'])->references(['Id'])->on('t_Countries')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ThirdParties', function (Blueprint $table) {
            $table->dropForeign('t_thirdparties_countryid_foreign');
            $table->dropForeign('t_thirdparties_createdby_foreign');
            $table->dropForeign('t_thirdparties_deletedby_foreign');
            $table->dropForeign('t_thirdparties_modifiedby_foreign');
        });
    }
};
