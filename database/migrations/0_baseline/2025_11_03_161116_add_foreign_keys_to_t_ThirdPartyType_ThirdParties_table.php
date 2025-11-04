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
        Schema::table('t_ThirdPartyType_ThirdParties', function (Blueprint $table) {
            $table->foreign(['CreatedBy'], 'fk_tptp_createdby_tpu')->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'], 'fk_tptp_deletedby_tpu')->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'], 'fk_tptp_modifiedby_tpu')->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ThirdPartyId'])->references(['Id'])->on('t_ThirdParties')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['TypeId'])->references(['TypeId'])->on('t_ThirdPartyTypes')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ThirdPartyType_ThirdParties', function (Blueprint $table) {
            $table->dropForeign('fk_tptp_createdby_tpu');
            $table->dropForeign('fk_tptp_deletedby_tpu');
            $table->dropForeign('fk_tptp_modifiedby_tpu');
            $table->dropForeign('t_thirdpartytype_thirdparties_thirdpartyid_foreign');
            $table->dropForeign('t_thirdpartytype_thirdparties_typeid_foreign');
        });
    }
};
