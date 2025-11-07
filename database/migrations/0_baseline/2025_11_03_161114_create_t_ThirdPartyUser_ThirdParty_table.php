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
        Schema::create('t_ThirdPartyUser_ThirdParty', function (Blueprint $table) {
            $table->bigInteger('third_party_user_id');
            $table->bigInteger('third_party_id');

            $table->primary(['third_party_user_id', 'third_party_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ThirdPartyUser_ThirdParty');
    }
};
