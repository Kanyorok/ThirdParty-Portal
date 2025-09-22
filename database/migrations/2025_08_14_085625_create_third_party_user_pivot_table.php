<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_ThirdPartyUsers', function (Blueprint $table) {
            $table->dropForeign(['ThirdPartyId']);
            $table->dropColumn('ThirdPartyId');
        });

        Schema::create('t_ThirdPartyUser_ThirdParty', function (Blueprint $table) {
            $table->unsignedBigInteger('third_party_user_id');
            $table->unsignedBigInteger('third_party_id');
            $table->primary(['third_party_user_id', 'third_party_id']);
            $table->foreign('third_party_user_id')->references('Id')->on('t_ThirdPartyUsers')->onDelete('cascade');
            $table->foreign('third_party_id')->references('Id')->on('t_ThirdParties')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_ThirdPartyUser_ThirdParty');
        Schema::table('t_ThirdPartyUsers', function (Blueprint $table) {
            $table->unsignedBigInteger('ThirdPartyId')->nullable();
            $table->foreign('ThirdPartyId')->references('Id')->on('t_ThirdParties');
        });
    }
};
