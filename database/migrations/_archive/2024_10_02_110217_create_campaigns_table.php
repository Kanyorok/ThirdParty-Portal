<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_Campaigns', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('CampaignID')->unique();
            $table->string('Label');
            $table->char('Status', 1);
            $table->char('Type', 1);
            $table->boolean('Processing')->default(false);
            $table->longText('Details')->nullable();//email, sms, content
            $table->longText('Notes')->nullable();
            $table->foreignId('MarketingListId')->constrained('t_MarketingLists', 'MarketingListID');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_CampaignParties', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('CampaignId')->constrained('t_Campaigns', 'Id')->cascadeOnUpdate()->cascadeOnUpdate();
            $table->string("Party");
            $table->string("PartyID", 100);
            $table->char('Status', 1);
            $table->string("Channel")->nullable();
            $table->string("ChannelID", 100)->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');

            $table->index(["Party", "PartyID"]);
            $table->index(["Channel", "ChannelID"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_CampaignParties');
        Schema::dropIfExists('t_Campaigns');
    }
};
