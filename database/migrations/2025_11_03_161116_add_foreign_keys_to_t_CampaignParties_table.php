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
        Schema::table('t_CampaignParties', function (Blueprint $table) {
            $table->foreign(['CampaignId'])->references(['Id'])->on('t_Campaigns')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_CampaignParties', function (Blueprint $table) {
            $table->dropForeign('t_campaignparties_campaignid_foreign');
            $table->dropForeign('t_campaignparties_createdby_foreign');
            $table->dropForeign('t_campaignparties_modifiedby_foreign');
        });
    }
};
