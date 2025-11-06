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
        Schema::table('t_ThirdPartyUser_ThirdParty', function (Blueprint $table) {
            $table->foreign(['third_party_id'])->references(['Id'])->on('t_ThirdParties')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['third_party_user_id'])->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ThirdPartyUser_ThirdParty', function (Blueprint $table) {
            $table->dropForeign('t_thirdpartyuser_thirdparty_third_party_id_foreign');
            $table->dropForeign('t_thirdpartyuser_thirdparty_third_party_user_id_foreign');
        });
    }
};
