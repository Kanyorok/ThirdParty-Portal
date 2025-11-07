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
        Schema::create('t_CampaignParties', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('CampaignId');
            $table->string('Party');
            $table->string('PartyID', 100);
            $table->char('Status', 1);
            $table->string('Channel')->nullable();
            $table->string('ChannelID', 100)->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');

            $table->primary(['Id'], 'pk__t_campai__3214ec074758a1fc');
            $table->index(['Channel', 'ChannelID']);
            $table->index(['Party', 'PartyID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_CampaignParties');
    }
};
