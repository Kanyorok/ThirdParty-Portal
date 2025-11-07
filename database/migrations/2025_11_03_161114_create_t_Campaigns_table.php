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
        Schema::create('t_Campaigns', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('CampaignID')->unique();
            $table->string('Label');
            $table->char('Status', 1);
            $table->char('Type', 1);
            $table->boolean('Processing')->default(false);
            $table->text('Details')->nullable();
            $table->text('Notes')->nullable();
            $table->bigInteger('MarketingListId');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_campai__3214ec0762296528');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Campaigns');
    }
};
