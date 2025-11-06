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
        Schema::table('t_ThirdPartiesBankDetails', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ThirdPartyId'])->references(['Id'])->on('t_ThirdParties')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ThirdPartiesBankDetails', function (Blueprint $table) {
            $table->dropForeign('t_thirdpartiesbankdetails_createdby_foreign');
            $table->dropForeign('t_thirdpartiesbankdetails_deletedby_foreign');
            $table->dropForeign('t_thirdpartiesbankdetails_modifiedby_foreign');
            $table->dropForeign('t_thirdpartiesbankdetails_thirdpartyid_foreign');
        });
    }
};
